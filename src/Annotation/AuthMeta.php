<?php

namespace Zxin\Think\Auth\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * 节点描述
 * @package Zxin\Think\Auth\Annotation
 * @Annotation
 * @Annotation\Target({"CLASS", "METHOD"})
 * @NamedArgumentConstructor
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class AuthMeta extends Base
{
    /**
     * 功能注解
     */
    public string $desc;

    /**
     * 定义策略
     */
    public string $policy;

    public function __construct(string $desc, string $policy = '')
    {
        $this->desc = $desc;
        $this->policy = $policy;
    }
}
