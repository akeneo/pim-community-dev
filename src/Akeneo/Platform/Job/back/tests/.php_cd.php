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
        [
            'Webmozart\Assert\Assert',
        ],
    )->in('Akeneo\Platform\Job\Domain'),
    $builder->only(
        [
            'Akeneo\Platform\Job\Application',
            'Akeneo\Platform\Job\Domain',
            'Akeneo\Tool',
            'Doctrine\DBAL\Connection',
            'Doctrine\DBAL\Types',
            'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
            'Oro\Bundle\SecurityBundle\SecurityFacade',
            'Symfony\Component',
            'Symfony\Contracts',
            'Webmozart\Assert\Assert',
        ],
    )->in('Akeneo\Platform\Job\Infrastructure'),
];

return new Configuration($rules, $finder);
