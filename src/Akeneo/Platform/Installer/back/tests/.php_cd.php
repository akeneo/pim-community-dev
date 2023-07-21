<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
        ],
    )->in('Akeneo\Platform\Installer\Domain'),

    $builder->only(
        [
            'Akeneo\Platform\Installer\Domain',
        ],
    )->in('Akeneo\Platform\Installer\Application'),

    $builder->only(
        [
            'Akeneo\Platform\Installer\Application',
            'Akeneo\Platform\Installer\Domain',

            'Akeneo\Platform\Bundle\InstallerBundle\Event',
            'Akeneo\Platform\Job\ServiceApi',
            'Akeneo\Tool',
            'Doctrine\DBAL\Connection',
            'Doctrine\DBAL\Schema\AbstractAsset',
            'Doctrine\DBAL\Types\Types',
            'League\Flysystem\FilesystemOperator',
            'Symfony\Component',
            'Webmozart\Assert\Assert',
            'Oro\Bundle\SecurityBundle\SecurityFacade',
        ],
    )->in('Akeneo\Platform\Installer\Infrastructure'),
];

return new Configuration($rules, $finder);
