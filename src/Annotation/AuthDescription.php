<?php
declare(strict_types=1);

namespace Zxin\Think\Auth\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * 权限描述
 * @package Zxin\Think\Auth\Annotation
 * @Annotation
 * @Annotation\Target({"CLASS", "METHOD"})
 */
final class AuthDescription extends Annotation
{
    /**
     * 功能注解
     *
     * @var string
     */
    public $value = '';
}
