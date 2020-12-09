<?php
declare(strict_types=1);

namespace Zxin\Think\Auth;

use think\Container;
use Zxin\Think\Auth\Access\Gate;
use Zxin\Think\Auth\Contracts\Authenticatable;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use think\App;

class Service extends \think\Service
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     */
    public function register()
    {
        $middleware = $this->app->config->get('auth.middleware');
        if ($middleware && class_exists($middleware)) {
            $this->app->middleware->add($middleware, 'route');
        }
        $this->app->bind('auth', AuthGuard::class);
        $this->app->bind('auth.permission', Permission::class);
        $this->app->make('auth.permission');
        $this->registerAccessGate();

        // TODO: this method is deprecated and will be removed in doctrine/annotations 2.0
        AnnotationRegistry::registerLoader('class_exists');
    }

    public function boot()
    {
    }

    protected function registerAccessGate()
    {
        $this->app->bind('auth.gate', Gate::class);
        $this->app->bind(Gate::class, function (App $app) {
            $gate = (new Gate($app, function () use ($app) {
                return $app->make('auth')->user();
            }));
            $this->registerUriGateAbilities($gate, $app);
            return $gate;
        });
    }

    protected function registerUriGateAbilities(Gate $gate, Container $container)
    {
        $gate->define(Permission::class, function (Authenticatable $user, string $uri) {
            return isset($user->permissions()[$uri]);
        });
        $gate->before(function (Authenticatable $user, string $uri) use ($gate, $container) {
            $permissionObject = Permission::getInstance();
            if ($user->isIgnoreAuthentication()) {
                AuthContext::createSuperRoot($uri);
                return true;
            }
            if (!$gate->has($uri) && $permissionObject->contain($uri)) {
                $permissions = $permissionObject->getPermissionsByFeature($uri) ?? $uri;
                foreach ($permissions as $permission => $true) {
                    if ($user->allowPermission($permission)) {
                        AuthContext::create($uri, [$permission], true);
                        return true;
                    }
                }
                AuthContext::create($uri, $permissions, false);
                return false;
            }
            return null;
        });
    }
}
