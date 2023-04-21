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
            'Akeneo\Platform\Installer\Infrastructure\CommandExecutor\CommandExecutorInterface',
            'Akeneo\Tool\Component\Batch\Model\JobInstance',
            'Symfony\Component\EventDispatcher\GenericEvent',
        ],
    )->in('Akeneo\Platform\Installer\Domain'),

    $builder->only(
        [
            'Akeneo\Platform\Installer\Domain\CommandExecutor',
            'Akeneo\Platform\Installer\Domain\Event',
            'Akeneo\Platform\Installer\Domain\Query',
            'Akeneo\Platform\Job\ServiceApi',
            'Doctrine\ORM\EntityManagerInterface',
            'Symfony\Component',
        ],
    )->in('Akeneo\Platform\Installer\Application'),

    $builder->only(
        [
            'Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage',
            'Akeneo\Platform\Installer\Application',
            'Akeneo\Platform\Installer\Domain',
            'Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry',
            'Akeneo\Tool\Component',
            'Doctrine\DBAL',
            'Symfony\Bundle\FrameworkBundle\Console\Application',
            'Symfony\Component',
        ],
    )->in('Akeneo\Platform\Installer\Infrastructure'),
];

return new Configuration($rules, $finder);
