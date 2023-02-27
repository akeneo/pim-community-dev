<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php80\Rector\FunctionLike\UnionTypesRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

    $rectorConfig->phpstanConfig(__DIR__.'/phpstan.neon');

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::TYPE_DECLARATION,
        LevelSetList::UP_TO_PHP_80,
    ]);

    $rectorConfig->rules([
        MakeInheritedMethodVisibilitySameAsParentRector::class,
    ]);

    $rectorConfig->skip([
        FlipTypeControlToUseExclusiveTypeRector::class,
        CountArrayToEmptyArrayComparisonRector::class,
        AddLiteralSeparatorToNumberRector::class,
        StringClassNameToClassConstantRector::class => [
            __DIR__ . '/tests/Acceptance/AuthenticationContext.php',
            __DIR__ . '/tests/Integration/IntegrationTestCase.php',
        ],

        // TODO: Remove next lines
        UnionTypesRector::class,
        AddArrayParamDocTypeRector::class,
        AddArrayReturnDocTypeRector::class,
    ]);
};
