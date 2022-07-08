<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('tests');
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            'Akeneo\Platform\Bundle\ImportExportBundle\Domain',
            'Webmozart\Assert\Assert',
        ],
    )->in('Akeneo\Platform\Bundle\ImportExportBundle\Application'),
    $builder->only(
        [
            /** We should not need to be coupled with Application layer */
            'Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\FileToTransfer',

            'Webmozart\Assert\Assert',
        ],
    )->in('Akeneo\Platform\Bundle\ImportExportBundle\Domain'),
    $builder->only(
        [
            'Akeneo\Tool',
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags',
            'Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface',

            'Akeneo\Platform\Bundle\ImportExportBundle\Application',
            'Akeneo\Platform\Bundle\ImportExportBundle\Domain',

            'League\Flysystem',
            'Symfony\Component',
            'Symfony\Contracts',
            'Webmozart\Assert\Assert',
        ],
    )->in('Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure'),
];

return new Configuration($rules, $finder);
