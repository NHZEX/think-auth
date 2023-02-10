<?php

declare(strict_types=1);

namespace Zxin\Think\Auth\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * 权限注解
 * @package Zxin\Think\Auth\Annotation
 * @Annotation
 * @Annotation\Target({"CLASS", "METHOD"})
 * @NamedArgumentConstructor
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Auth extends Base
{
    /**
     * 定义权限分配
     */
    public string $name;

    /**
     * 定义权限策略
     */
    public string $policy;

    public function __construct(
        string $name = 'login',
        string $policy = ''
    ) {
        $this->policy = $policy;
        $this->name   = $name;
    }
}
