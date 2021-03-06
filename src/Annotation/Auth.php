<?php
declare(strict_types=1);

namespace Zxin\Think\Auth\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * 权限注解
 * @package Zxin\Think\Auth\Annotation
 * @Annotation
 * @Annotation\Target({"CLASS", "METHOD"})
 */
final class Auth extends Annotation
{
    /**
     * 定义权限分配
     *
     * @var string
     */
    public $value = 'login';

    /**
     * 定义策略
     *
     * @var string
     */
    public $policy = '';
}
