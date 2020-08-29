<?php
declare(strict_types=1);

namespace Zxin\Think\Auth;

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
            $this->registerUriGateAbilities($gate);
            return $gate;
        });
    }

    protected function registerUriGateAbilities(Gate $gate)
    {
        $gate->define(Permission::class, function (Authenticatable $user, string $uri) {
            return isset($user->permissions()[$uri]);
        });
        $gate->before(function (Authenticatable $user, string $uri) use ($gate) {
            if (!$gate->has($uri) && Permission::getInstance()->contain($uri)) {
                $permissions = Permission::getInstance()->getPermissionsByFeature($uri) ?? $uri;
                foreach ($permissions as $permission => $true) {
                    if ($user->allowPermission($permission)) {
                        // 权限授予
                        return true;
                    }
                }
                return false;
            }
            return null;
        });
    }
}
