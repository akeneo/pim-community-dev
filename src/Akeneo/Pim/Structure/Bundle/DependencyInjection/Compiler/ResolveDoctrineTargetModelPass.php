<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeTranslationInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupTranslationInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeTranslationInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslationInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantTranslationInterface;
use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;
use Akeneo\Pim\Structure\Component\Model\GroupTypeTranslationInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
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
            AttributeInterface::class                 => 'pim_catalog.entity.attribute.class',
            AttributeTranslationInterface::class      => 'pim_catalog.entity.attribute_translation.class',
            AttributeOptionInterface::class           => 'pim_catalog.entity.attribute_option.class',
            AttributeGroupInterface::class            => 'pim_catalog.entity.attribute_group.class',
            AttributeGroupTranslationInterface::class => 'pim_catalog.entity.attribute_group_translation.class',
            AttributeRequirementInterface::class      => 'pim_catalog.entity.attribute_requirement.class',
            FamilyInterface::class                    => 'pim_catalog.entity.family.class',
            FamilyTranslationInterface::class         => 'pim_catalog.entity.family_translation.class',
            FamilyVariantInterface::class             => 'pim_catalog.entity.family_variant.class',
            FamilyVariantTranslationInterface::class  => 'pim_catalog.entity.family_variant_translation.class',
            VariantAttributeSetInterface::class       => 'pim_catalog.entity.variant_attribute_set.class',
            GroupTypeInterface::class => 'pim_catalog.entity.group_type.class',
            GroupTypeTranslationInterface::class => 'pim_catalog.entity.group_type_translation.class',
            AssociationTypeTranslationInterface::class => 'pim_catalog.entity.association_type_translation.class',
            AssociationTypeInterface::class => 'pim_catalog.entity.association_type.class',
            AttributeOptionValueInterface::class => 'pim_catalog.entity.attribute_option_value.class',
        ];
    }
}
