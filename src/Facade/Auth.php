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
 * @method int userGenre() static
 * @method int userRoleId() static
 * @method Authenticatable user() static
 * @method bool check() static
 * @method bool can(string $name) static
 */
class Auth extends Facade
{
    protected static function getFacadeClass()
    {
        return 'auth';
    }
}
