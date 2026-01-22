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
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',
        ],
    )->in('Akeneo\Platform\Installer\Application'),

    $builder->only(
        [
            'Akeneo\Platform\Installer\Application',
            'Akeneo\Platform\Installer\Domain',

            'Akeneo\Platform\Installer\Infrastructure\Event',
            'Akeneo\Platform\Job\ServiceApi',
            'Akeneo\Tool',
            'Doctrine\DBAL\Connection',
            'Doctrine\DBAL\Exception\TableNotFoundException',
            'Doctrine\DBAL\Schema\AbstractAsset',
            'Doctrine\DBAL\Types\Types',
            'Doctrine\ORM\EntityManagerInterface',
            'Doctrine\Persistence\ObjectRepository',
            'League\Flysystem\FilesystemOperator',
            'Psr\Log\LoggerInterface',
            'Symfony\Component',
            'Webmozart\Assert\Assert',
            'Oro\Bundle\SecurityBundle\SecurityFacade',
            'Akeneo\Platform\Requirements',
            'Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage',
        ],
    )->in('Akeneo\Platform\Installer\Infrastructure'),
];

return new Configuration($rules, $finder);
