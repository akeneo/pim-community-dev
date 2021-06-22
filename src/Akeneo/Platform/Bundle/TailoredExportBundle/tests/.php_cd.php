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
            'Symfony\Component',
            'Symfony\Contracts',
            'Akeneo\Tool',
            'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
            'Oro\Bundle\SecurityBundle\SecurityFacade',
            'Webmozart\Assert\Assert',
            'Doctrine\DBAL\Connection',
            'Box\Spout\Writer\WriterFactory',
            'Box\Spout\Writer\WriterInterface',

            'Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\FindAssetLabelTranslation',
            'Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface',
            'Akeneo\Pim\Structure\Component\Query\PublicApi',
            'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators',
            'Akeneo\Pim\Permission\Bundle\User\UserContext',
            'Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindRecordsLabelTranslations',
            'Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface',
        ]
    )->in('Akeneo\Platform\TailoredExport'),
];

return new Configuration($rules, $finder);
