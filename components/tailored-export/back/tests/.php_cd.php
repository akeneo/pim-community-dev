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
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            'Webmozart\Assert\Assert',
            'Akeneo\Platform\TailoredExport\Domain',
        ],
    )->in('Akeneo\Platform\TailoredExport\Application'),
    $builder->only(
        [
            'Ramsey\Uuid\UuidInterface',
        ],
    )->in('Akeneo\Platform\TailoredExport\Domain'),
    $builder->only(
        [
            'Symfony\Component',
            'Symfony\Contracts',
            'Akeneo\Tool',
            'Webmozart\Assert\Assert',
            'OpenSpout\Writer\Common\Creator\WriterEntityFactory',
            'OpenSpout\Writer\Common\Creator\WriterFactory',
            'OpenSpout\Writer\WriterInterface',
            'Ramsey\Uuid\UuidInterface',

            'Akeneo\Platform\TailoredExport\Application',
            'Akeneo\Platform\TailoredExport\Domain',

            'Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage',

            'Akeneo\AssetManager\Infrastructure\PublicApi',
            'Akeneo\Channel\API',
            'Akeneo\Channel\Infrastructure\Component\Query\PublicApi',
            'Akeneo\Pim\Automation\DataQualityInsights\PublicApi',
            'Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators',
            'Akeneo\Pim\Enrichment\Component\Product\Query\GetProductLabelsInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue',
            'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
            'Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface',
            'Akeneo\Pim\Structure\Component\Query\PublicApi',
            'Akeneo\Pim\TableAttribute\Domain\Value\Table',
            'Akeneo\ReferenceEntity\Infrastructure\PublicApi',
            'Akeneo\UserManagement\Component\Model\UserInterface',
        ],
    )->in('Akeneo\Platform\TailoredExport\Infrastructure'),
];

return new Configuration($rules, $finder);
