<?php

declare(strict_types=1);

namespace Zxin\Think\Auth\Contracts;

interface ProviderlSelfCheck
{
    public function valid(&$message): bool;
}
