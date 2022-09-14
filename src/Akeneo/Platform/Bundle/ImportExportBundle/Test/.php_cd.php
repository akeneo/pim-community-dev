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
            // We should not need to be coupled with Application layer
            'Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\FileToTransfer',
        ],
    )->in('Akeneo\Platform\Bundle\ImportExportBundle\Domain'),
    $builder->only(
        [
            'Akeneo\Platform\Bundle\ImportExportBundle\Domain',
        ],
    )->in('Akeneo\Platform\Bundle\ImportExportBundle\Application'),
    $builder->only(
        [
            'Akeneo\Platform\Bundle\ImportExportBundle\Application',
            'Akeneo\Platform\Bundle\ImportExportBundle\Domain',

            'Akeneo\UserManagement\ServiceApi',
            'Akeneo\Tool',

            'League\Flysystem',
            'Symfony\Component',
            'Symfony\Contracts',
        ],
    )->in('Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure'),
];

return new Configuration($rules, $finder);
