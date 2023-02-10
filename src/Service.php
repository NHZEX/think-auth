<?php

declare(strict_types=1);

namespace Zxin\Think\Auth;

use Closure;
use RuntimeException;
use think\App;
use Zxin\Think\Auth\Access\Gate;
use Zxin\Think\Auth\Contracts\Authenticatable;
use Zxin\Think\Auth\Contracts\Guard;

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
        $this->app->bind(Guard::class, fn (App $app) => $this->guardInstance($app));
        $this->app->bind(AuthGuard::class, Guard::class);
        $this->app->bind('auth', Guard::class);
    }

    protected function guardInstance(App $app): Guard
    {
        $guard = $app->config->get('auth.guardProvider');
        if (empty($guard)) {
            return $app->invokeClass(AuthGuard::class);
        } elseif (\is_string($guard)) {
            if (is_subclass_of($guard, Guard::class)) {
                return $app->invokeClass($guard);
            } else {
                throw new RuntimeException("invalid guard: {$guard}");
            }
        } elseif ($guard instanceof Closure) {
            $instance = $app->invokeFunction($guard);
            if (!\is_object($instance)) {
                throw new RuntimeException("invalid guard, not an object");
            }
            if (!($instance instanceof Guard)) {
                throw new RuntimeException('invalid guard: ' . \get_class($instance));
            }
            return $instance;
        } else {
            throw new RuntimeException("invalid guard provider");
        }
    }

    protected function registerAccessGate()
    {
        $this->app->bind('auth.gate', Gate::class);
        $this->app->bind(Gate::class, function (App $app) {
            $gate = (new Gate($app, fn () => $app->make('auth')->user()));
            $this->registerUriGateAbilities($gate);
            return $gate;
        });
    }

    protected function registerUriGateAbilities(Gate $gate)
    {
        $gate->define(Permission::class, fn (Authenticatable $user, string $uri) => isset($user->permissions()[$uri]));
        $gate->before(function (Authenticatable $user, string $uri) use ($gate) {
            if ($user->isIgnoreAuthentication()) {
                AuthContext::createSuperRoot($uri);
                return true;
            }
            $permissionObject = Permission::getInstance();
            if (!$gate->has($uri) && $permissionObject->contain($uri)) {
                $permissions = $permissionObject->getPermissionsByFeature($uri) ?? [];
                foreach ($permissions as $permission => $_) {
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
