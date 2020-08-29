<?php
declare(strict_types=1);

namespace Zxin\Think\Auth\Facade;

use app\Model\AdminUser;
use Zxin\Think\Auth\AuthGuard;
use think\Facade;

/**
 * Class Auth
 * @package Zxin\Think\Auth\Facade
 * @method AuthGuard instance() static
 * @method int|string id() static
 * @method int userGenre() static
 * @method int userRoleId() static
 * @method AdminUser user() static
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
