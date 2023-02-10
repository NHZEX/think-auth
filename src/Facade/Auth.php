<?php

declare(strict_types=1);

namespace Zxin\Think\Auth\Facade;

use think\Facade;
use Zxin\Think\Auth\AuthGuard;
use Zxin\Think\Auth\Contracts\Authenticatable;

/**
 * Class Auth
 * @package Zxin\Think\Auth\Facade
 * @method static AuthGuard instance()
 * @method static int|string id()
 * @method static Authenticatable user()
 * @method static bool check()
 */
class Auth extends Facade
{
    protected static function getFacadeClass()
    {
        return 'auth';
    }
}
