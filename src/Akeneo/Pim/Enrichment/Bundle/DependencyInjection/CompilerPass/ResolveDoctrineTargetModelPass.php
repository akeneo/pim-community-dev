<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection\CompilerPass;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductUniqueDataInterface;
use Akeneo\Tool\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass;

/**
 * Resolves doctrine ORM Target entities
 *
 * @author    Arnaud Langlade <arnaud.langlade@gmail.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResolveDoctrineTargetModelPass extends AbstractResolveDoctrineTargetModelPass
{
    /**
     * {@inheritdoc}
     */
    protected function getParametersMapping(): array
    {
        return [
            CategoryInterface::class => 'pim_catalog.entity.category.class',
            CompletenessInterface::class => 'pim_catalog.entity.completeness.class',
            ProductInterface::class => 'pim_catalog.entity.product.class',
            ProductAssociationInterface::class => 'pim_catalog.entity.association.class',
            ProductModelInterface::class => 'pim_catalog.entity.product_model.class',
            ProductModelAssociationInterface::class => 'pim_catalog.entity.product_model_association.class',
            ProductUniqueDataInterface::class => 'pim_catalog.entity.product_unique_data.class',
        ];
    }
}
