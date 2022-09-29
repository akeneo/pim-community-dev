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
            'Akeneo\Platform\Job\Domain',
            'Webmozart\Assert\Assert',
        ],
    )->in('Akeneo\Platform\Job\Application'),
    $builder->only(
        [],
    )->in('Akeneo\Platform\Job\Domain'),
    $builder->only(
        [
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
            'Akeneo\Platform\Job\Application',
            'Akeneo\Platform\Job\Domain',
            'Akeneo\Platform\Job\ServiceApi',
            'Doctrine\DBAL\Connection',
            'Oro\Bundle\SecurityBundle\SecurityFacade',
            'Symfony\Component',
        ],
    )->in('Akeneo\Platform\Job\Infrastructure'),
];

return new Configuration($rules, $finder);
