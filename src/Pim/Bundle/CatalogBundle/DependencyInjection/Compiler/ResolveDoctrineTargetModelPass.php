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
            'Pim\Component\Catalog\Model\AssociationTypeInterface'           => 'pim_catalog.entity.association_type.class',
            'Pim\Component\Catalog\Model\AttributeOptionValueInterface'      => 'pim_catalog.entity.attribute_option_value.class',
            'Pim\Component\Catalog\Model\GroupInterface'                     => 'pim_catalog.entity.group.class',
            'Pim\Component\Catalog\Model\GroupTypeInterface'                 => 'pim_catalog.entity.group_type.class',
            'Pim\Component\Catalog\Model\CategoryInterface'                  => 'pim_catalog.entity.category.class',
            'Pim\Component\Catalog\Model\CategoryTranslationInterface'       => 'pim_catalog.entity.category_translation.class',
            'Pim\Component\Catalog\Model\AssociationTypeTranslationInterface'=> 'pim_catalog.entity.association_type_translation.class',
            'Pim\Component\Catalog\Model\GroupTranslationInterface'          => 'pim_catalog.entity.group_translation.class',
            'Pim\Component\Catalog\Model\GroupTypeTranslationInterface'      => 'pim_catalog.entity.group_type_translation.class',
        ];
    }
}
