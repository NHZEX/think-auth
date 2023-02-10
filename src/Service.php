<?php
declare(strict_types=1);

namespace Zxin\Think\Auth;

use think\App;
use think\Container;
use Zxin\Think\Auth\Access\Gate;
use Zxin\Think\Auth\Contracts\Authenticatable;
use Zxin\Think\Auth\Contracts\Guard;
use function array_keys;
use function class_exists;
use function get_class;
use function is_object;
use function is_string;
use function is_subclass_of;

class Service extends \think\Service
{
    /**
     */
    public function register()
    {
        $middleware = $this->app->config->get('auth.middleware');
        if ($middleware && class_exists($middleware)) {
            $this->app->middleware->add($middleware, 'route');
        }
        $this->registerGuard();
        $this->app->bind('auth.permission', Permission::class);
        $this->registerAccessGate();
    }

    public function boot()
    {
        Permission::getInstance();
    }

    protected function registerGuard()
    {
        $this->app->bind(Guard::class, function (App $app) {
            return $this->guardInstance($app);
        });
        $this->app->bind(AuthGuard::class, Guard::class);
        $this->app->bind('auth', Guard::class);
    }

    protected function guardInstance(App $app): Guard
    {
        $guard = $app->config->get('auth.guardProvider');
        if (empty($guard)) {
            return $app->invokeClass(AuthGuard::class);
        } elseif (is_string($guard)) {
            if (is_subclass_of($guard, Guard::class)) {
                return $app->invokeClass($guard);
            } else {
                throw new \RuntimeException("invalid guard: {$guard}");
            }
        } elseif ($guard instanceof \Closure) {
            $instance = $app->invokeFunction($guard);
            if (!is_object($instance)) {
                throw new \RuntimeException("invalid guard, not an object");
            }
            if (!($instance instanceof Guard)) {
                throw new \RuntimeException('invalid guard: ' . get_class($instance));
            }
            return $instance;
        } else {
            throw new \RuntimeException("invalid guard provider");
        }
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
            if ($user->isIgnoreAuthentication()) {
                AuthContext::createSuperRoot($uri);
                return true;
            }
            $permissionObject = Permission::getInstance();
            if (!$gate->has($uri) && $permissionObject->contain($uri)) {
                $permissions = $permissionObject->getPermissionsByFeature($uri) ?? [];
                foreach ($permissions as $permission => $true) {
                    if ($user->allowPermission($permission)) {
                        AuthContext::create($uri, [$permission], true);
                        return true;
                    }
                }
                AuthContext::create($uri, array_keys($permissions), false);
                return false;
            }
            return null;
        });
    }
}
