<?php

namespace Zxin\Think\Auth;

use think\Container;
use Zxin\Think\Auth\Contracts\Authenticatable;

class AuthManager
{
    /**
     * @return AuthGuard
     */
    public static function instance(): AuthGuard
    {
        return Container::getInstance()->make(AuthGuard::class);
    }

    /**
     * @return int|string|null
     */
    public static function id()
    {
        return self::instance()->id();
    }

    /**
     */
    public static function user(): ?Authenticatable
    {
        return self::instance()->user();
    }

    /**
     * @return bool
     */
    public static function check(): bool
    {
        return self::instance()->check();
    }
}
