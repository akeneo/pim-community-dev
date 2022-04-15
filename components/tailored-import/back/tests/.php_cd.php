<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            'Symfony\Component\Validator\ConstraintViolationInterface',
            'Webmozart\Assert\Assert',
            // TODO: Write more specific rules later
            'Akeneo\Pim\Enrichment\Product\API',
        ],
    )->in('Akeneo\Platform\TailoredImport\Domain'),

    $builder->only(
        [
            'Akeneo\Platform\TailoredImport\Domain',
            // TODO: Write more specific rules later
            'Akeneo\Pim\Enrichment\Product\API',
        ],
    )->in('Akeneo\Platform\TailoredImport\Application'),

    $builder->only(
        [
            'Box\Spout\Common',
            'Box\Spout\Reader',
            'Symfony\Component',
            'Symfony\Contracts',
            'Webmozart\Assert\Assert',
            'Ramsey\Uuid\Uuid',
            'Akeneo\Platform\TailoredImport\Application',
            'Akeneo\Platform\TailoredImport\Domain',

            'Akeneo\Channel\Infrastructure\Component\Query\PublicApi',
            'Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand',
            'Akeneo\Pim\Structure\Component\Query\PublicApi',
            'Akeneo\Tool',
            'Akeneo\UserManagement\Component\Model\UserInterface',
            'Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException',
            'Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException'
        ],
    )->in('Akeneo\Platform\TailoredImport\Infrastructure'),
];

return new Configuration($rules, $finder);
