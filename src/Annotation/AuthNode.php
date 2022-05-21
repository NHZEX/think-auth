<?php
declare(strict_types=1);

namespace Zxin\Think\Auth\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * 权限节点
 * @package Zxin\Think\Auth\Annotation
 * @Annotation
 * @Annotation\Target({"CLASS", "METHOD"})
 * @deprecated()
 * @NamedArgumentConstructor
 */
final class AuthNode extends Base
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
