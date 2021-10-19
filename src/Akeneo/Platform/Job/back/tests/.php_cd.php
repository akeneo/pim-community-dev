<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
            'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
            'Oro\Bundle\SecurityBundle\SecurityFacade',
            'Symfony\Component',
            'Symfony\Contracts',
            'Webmozart\Assert\Assert',
        ],
    )->in('Akeneo\Platform\Job\Infrastructure'),
];

return new Configuration($rules, $finder);
