<?php
declare(strict_types=1);

namespace Zxin\Think\Auth\Facade;

use Zxin\Think\Auth\AuthGuard;
use think\Facade;
use Zxin\Think\Auth\Contracts\Authenticatable;

/**
 * Class Auth
 * @package Zxin\Think\Auth\Facade
 * @method AuthGuard instance() static
 * @method int|string id() static
 * @method Authenticatable user() static
 * @method bool check() static
 */
class Auth extends Facade
{
    protected static function getFacadeClass()
    {
        return 'auth';
    }
}
