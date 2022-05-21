<?php

namespace Zxin\Think\Auth\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * 节点描述
 * @package Zxin\Think\Auth\Annotation
 * @Annotation
 * @Annotation\Target({"CLASS", "METHOD"})
 * @NamedArgumentConstructor
 */
final class AuthMeta extends Base
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
