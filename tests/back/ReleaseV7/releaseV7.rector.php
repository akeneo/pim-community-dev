<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Rector\Symfony\Set\SymfonyLevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        SymfonyLevelSetList::UP_TO_SYMFONY_60
    ]);

    /** This rule is a workaround because symfony32.php add priority to addCompilerPass parameter */
    $rectorConfig->ruleWithConfiguration(
        ArgumentRemoverRector::class,
        [
            new ArgumentRemover('Symfony\\Component\\DependencyInjection\\ContainerBuilder', 'addCompilerPass', 1, ['\Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION']),
            new ArgumentRemover('Symfony\\Component\\DependencyInjection\\ContainerBuilder', 'addCompilerPass', 2, [0]),
        ],
    );

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);

    // Uncomment to troubleshoot failures (https://github.com/rectorphp/rector/blob/main/docs/how_to_troubleshoot_parallel_issues.md)
//    $rectorConfig->disableParallel();
};
