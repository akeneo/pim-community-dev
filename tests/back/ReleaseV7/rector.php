<?php

declare(strict_types=1);

use Rector\Arguments\Rector\MethodCall\RemoveMethodCallParamRector;
use Rector\Arguments\ValueObject\RemoveMethodCallParam;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Rector\Symfony\Set\SymfonyLevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        SymfonyLevelSetList::UP_TO_SYMFONY_60
    ]);

    /**
     * TODO: Investigate why symfony32.php rule add default value as parameters in *Bundle.php files (cf AkeneoAssetManagerBundle.php)
     * This rule is a workaround for this unwanted behaviour
     */
//    $rectorConfig->ruleWithConfiguration(
//        RemoveMethodCallParamRector::class,
//        [
//            new RemoveMethodCallParam('ContainerBuilder', 'addCompilerPass', 1),
//            new RemoveMethodCallParam('ContainerBuilder', 'addCompilerPass', 2),
//        ],
//    );
//    $rectorConfig->ruleWithConfiguration(
//        ArgumentRemoverRector::class,
//        [
//            new ArgumentRemover('ContainerBuilder', 'addCompilerPass', 1, [\Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION]),
//            new ArgumentRemover('ContainerBuilder', 'addCompilerPass', 2, [0]),
//        ],
//    );

    $paths = [
        __DIR__ . '/../../../src',
//        __DIR__ . '/../../../tests',
//        __DIR__ . '/../../../components',
//        __DIR__ . '/../../../grth/src',
//        __DIR__ . '/../../../grth/tests',
//        __DIR__ . '/../../../tria/src',
    ];

    $rectorConfig->paths($paths);
    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);

    // Uncomment to troubleshoot failures (https://github.com/rectorphp/rector/blob/main/docs/how_to_troubleshoot_parallel_issues.md)
//    $rectorConfig->disableParallel();
};
