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
            'Webmozart\Assert\Assert',
            // TODO: Write more specific rules later
            'Akeneo\Pim\Enrichment\Product\API',
            'Akeneo\Pim\Structure\Family\ServiceAPI',
        ],
    )->in('Akeneo\Platform\TailoredImport\Domain'),

    $builder->only(
        [
            'Akeneo\Platform\TailoredImport\Domain',
            // TODO: Write more specific rules later
            'Akeneo\Pim\Enrichment\Product\API',

            // Create Tailored Import ServiceAPI
            'Symfony\Component\Routing\RouterInterface',
            'Akeneo\Platform\TailoredImport\ServiceApi',
            'Akeneo\Platform\Job\ServiceApi\JobInstance',
        ],
    )->in('Akeneo\Platform\TailoredImport\Application'),

    $builder->only(
        [
            'OpenSpout\Reader',
            'Symfony\Component',
            'Symfony\Contracts',
            'Webmozart\Assert\Assert',
            'Ramsey\Uuid\Uuid',
            'Akeneo\Platform\TailoredImport\Application',
            'Akeneo\Platform\TailoredImport\Domain',

            'Akeneo\Channel\API',
            'Akeneo\Channel\Infrastructure\Component\Query\PublicApi',
            'Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand',
            'Akeneo\Pim\Enrichment\Product\API\Event',
            'Akeneo\Pim\Structure\Component\Query\PublicApi',
            'Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage',
            'Akeneo\Tool',
            'Akeneo\UserManagement\Component\Model\UserInterface',
            'Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException',
            'Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException',

            // category bounded context
            'Akeneo\Category\Api\CategoryTree',
            'Akeneo\Category\Api\FindCategoryTrees',
            'Akeneo\Category\Api\GetCategoryChildrenCodesPerTreeInterface',

            // family bounded context
            'Akeneo\Pim\Structure\Family\ServiceAPI',

            // Reference Entity bounded context
            'Akeneo\ReferenceEntity\Infrastructure\PublicApi',
        ],
    )->in('Akeneo\Platform\TailoredImport\Infrastructure'),
];

return new Configuration($rules, $finder);
