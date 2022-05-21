<?php
declare(strict_types=1);

namespace Zxin\Think\Auth\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * 权限节点
 * @package Zxin\Think\Auth\Annotation
 * @Annotation
 * @Annotation\Target({"CLASS", "METHOD"})
 * @deprecated()
 */
final class AuthNode extends Annotation
{
    /**
     * 功能注解
     *
     * @var string
     */
    public $value = '';

    /**
     * 定义策略
     *
     * @var string
     */
    public $policy = '';
}
