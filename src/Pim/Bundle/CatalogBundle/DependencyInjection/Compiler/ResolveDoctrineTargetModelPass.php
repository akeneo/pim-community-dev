<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Akeneo\Tool\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass;

/**
 * Resolves doctrine ORM Target entities
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResolveDoctrineTargetModelPass extends AbstractResolveDoctrineTargetModelPass
{
    /**
     * {@inheritdoc}
     */
    protected function getParametersMapping()
    {
        return [
            'Pim\Component\Catalog\Model\ProductAssociationInterface'        => 'pim_catalog.entity.association.class',
            'Pim\Component\Catalog\Model\ProductModelAssociationInterface'   => 'pim_catalog.entity.product_model_association.class',
            'Pim\Component\Catalog\Model\GroupInterface'                     => 'pim_catalog.entity.group.class',
            'Pim\Component\Catalog\Model\CompletenessInterface'              => 'pim_catalog.entity.completeness.class',
            'Pim\Component\Catalog\Model\ProductInterface'                   => 'pim_catalog.entity.product.class',
            'Pim\Component\Catalog\Model\ProductModelInterface'              => 'pim_catalog.entity.product_model.class',
            'Pim\Component\Catalog\Model\ProductUniqueDataInterface'         => 'pim_catalog.entity.product_unique_data.class',
            'Pim\Component\Catalog\Model\CategoryInterface'                  => 'pim_catalog.entity.category.class',
            'Pim\Component\Catalog\Model\CategoryTranslationInterface'       => 'pim_catalog.entity.category_translation.class',
            'Pim\Component\Catalog\Model\GroupTranslationInterface'          => 'pim_catalog.entity.group_translation.class',
        ];
    }
}
