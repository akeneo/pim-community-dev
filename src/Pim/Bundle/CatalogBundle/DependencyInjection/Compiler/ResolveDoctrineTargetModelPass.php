<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass;

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
            'Symfony\Component\Security\Core\User\UserInterface'             => 'oro_user.entity.class',
            'Pim\Component\Catalog\Model\AssociationInterface'               => 'pim_catalog.entity.association.class',
            'Pim\Component\Catalog\Model\AssociationTypeInterface'           => 'pim_catalog.entity.association_type.class',
            'Pim\Component\Catalog\Model\AttributeInterface'                 => 'pim_catalog.entity.attribute.class',
            'Pim\Component\Catalog\Model\AttributeOptionInterface'           => 'pim_catalog.entity.attribute_option.class',
            'Pim\Component\Catalog\Model\AttributeOptionValueInterface'      => 'pim_catalog.entity.attribute_option_value.class',
            'Pim\Component\Catalog\Model\AttributeGroupInterface'            => 'pim_catalog.entity.attribute_group.class',
            'Pim\Component\Catalog\Model\AttributeRequirementInterface'      => 'pim_catalog.entity.attribute_requirement.class',
            'Pim\Component\Catalog\Model\GroupInterface'                     => 'pim_catalog.entity.group.class',
            'Pim\Component\Catalog\Model\GroupTypeInterface'                 => 'pim_catalog.entity.group_type.class',
            'Pim\Component\Catalog\Model\CompletenessInterface'              => 'pim_catalog.entity.completeness.class',
            'Pim\Component\Catalog\Model\LocaleInterface'                    => 'pim_catalog.entity.locale.class',
            'Pim\Component\Catalog\Model\ProductInterface'                   => 'pim_catalog.entity.abstract_product.class',
            'Pim\Component\Catalog\Model\VariantProductInterface'            => 'pim_catalog.entity.variant_product.class',
            'Pim\Component\Catalog\Model\ProductModelInterface'              => 'pim_catalog.entity.product_model.class',
            'Pim\Component\Catalog\Model\ProductUniqueDataInterface'         => 'pim_catalog.entity.product_unique_data.class',
            'Pim\Component\Catalog\Model\CategoryInterface'                  => 'pim_catalog.entity.category.class',
            'Pim\Component\Catalog\Model\CurrencyInterface'                  => 'pim_catalog.entity.currency.class',
            'Pim\Component\Catalog\Model\FamilyInterface'                    => 'pim_catalog.entity.family.class',
            'Pim\Component\Catalog\Model\ChannelInterface'                   => 'pim_catalog.entity.channel.class',
            'Pim\Component\Catalog\Model\CategoryTranslationInterface'       => 'pim_catalog.entity.category_translation.class',
            'Pim\Component\Catalog\Model\FamilyTranslationInterface'         => 'pim_catalog.entity.family_translation.class',
            'Pim\Component\Catalog\Model\AttributeGroupTranslationInterface' => 'pim_catalog.entity.attribute_group_translation.class',
            'Pim\Component\Catalog\Model\AttributeTranslationInterface'      => 'pim_catalog.entity.attribute_translation.class',
            'Pim\Component\Catalog\Model\AssociationTypeTranslationInterface'=> 'pim_catalog.entity.association_type_translation.class',
            'Pim\Component\Catalog\Model\GroupTranslationInterface'          => 'pim_catalog.entity.group_translation.class',
            'Pim\Component\Catalog\Model\GroupTypeTranslationInterface'      => 'pim_catalog.entity.group_type_translation.class',
            'Pim\Component\Catalog\Model\ChannelTranslationInterface'        => 'pim_catalog.entity.channel_translation.class',
            'Pim\Component\Catalog\Model\FamilyVariantInterface'             => 'pim_catalog.entity.family_variant.class',
            'Pim\Component\Catalog\Model\FamilyVariantTranslationInterface'  => 'pim_catalog.entity.family_variant_translation.class',
            'Pim\Component\Catalog\Model\VariantAttributeSetInterface'       => 'pim_catalog.entity.variant_attribute_set.class',
        ];
    }
}
