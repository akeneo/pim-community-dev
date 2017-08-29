<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\ValuesFiller;

use Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Manager\AttributeValuesResolverInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;

/**
 * Values filler for entities with a Family Variant.
 *
 * Their attributes come from attribute sets, and values can come from parent product models.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EntityWithFamilyVariantValuesFiller extends AbstractEntityWithFamilyValuesFiller
{
    /** @var EntityWithFamilyVariantAttributesProvider */
    private $attributesProvider;

    /**
     * @param EntityWithValuesBuilderInterface          $entityWithValuesBuilder
     * @param AttributeValuesResolverInterface          $valuesResolver
     * @param CurrencyRepositoryInterface               $currencyRepository
     * @param EntityWithFamilyVariantAttributesProvider $attributesProvider
     */
    public function __construct(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValuesResolverInterface $valuesResolver,
        CurrencyRepositoryInterface $currencyRepository,
        EntityWithFamilyVariantAttributesProvider $attributesProvider
    ) {
        parent::__construct($entityWithValuesBuilder, $valuesResolver, $currencyRepository);

        $this->attributesProvider = $attributesProvider;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkEntityType(EntityWithFamilyInterface $entity): void
    {
        if (!$entity instanceof EntityWithFamilyVariantInterface) {
            throw new \InvalidArgumentException(
                sprintf('%s expected, %s given', EntityWithFamilyVariantInterface::class, get_class($entity))
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedAttributes(EntityWithFamilyInterface $entity): array
    {
        $attributes = [];

        /** @var EntityWithFamilyVariantInterface $entity */
        foreach ($this->attributesProvider->getAttributes($entity) as $attribute) {
            $attributes[$attribute->getCode()] = $attribute;
        }

        if (null !== $entity->getParent()) {
            $parentAttributes = $this->getExpectedAttributes($entity->getParent());
            $attributes = array_merge($attributes, $parentAttributes);
        }

        return $attributes;
    }
}
