<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
    ]);
    $rectorConfig->bootstrapFiles([]);

    $rectorConfig->skip([
        JsonThrowOnErrorRector::class,
        AddLiteralSeparatorToNumberRector::class,
        // 避免大范围更改
        RemoveUselessParamTagRector::class,
        RemoveUselessReturnTagRector::class,
    ]);

    $rectorConfig->rules([]);

    // define sets of rules
    $rectorConfig->sets([
        SetList::DEAD_CODE,
        // SetList::CODE_QUALITY,
        LevelSetList::UP_TO_PHP_74,
    ]);

    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon');
};
