<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryTranslationInterface;
use Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupTranslationInterface;
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
            CategoryTranslationInterface::class => 'pim_catalog.entity.category_translation.class',
            GroupInterface::class => 'pim_catalog.entity.group.class',
            GroupTranslationInterface::class => 'pim_catalog.entity.group_translation.class',
            ProductInterface::class => 'pim_catalog.entity.product.class',
            ProductAssociationInterface::class => 'pim_catalog.entity.association.class',
            ProductModelInterface::class => 'pim_catalog.entity.product_model.class',
            ProductModelAssociationInterface::class => 'pim_catalog.entity.product_model_association.class',
            ProductUniqueDataInterface::class => 'pim_catalog.entity.product_unique_data.class',
            CommentInterface::class => 'pim_comment.entity.comment.class',
        ];
    }
}
