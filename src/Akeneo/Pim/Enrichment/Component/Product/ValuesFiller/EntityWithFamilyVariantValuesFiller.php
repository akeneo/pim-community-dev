<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\ValuesFiller;

use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

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

    public function __construct(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValuesResolverInterface $valuesResolver,
        CurrencyRepositoryInterface $currencyRepository,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        LruArrayAttributeRepository $attributeRepository
    ) {
        parent::__construct($entityWithValuesBuilder, $valuesResolver, $currencyRepository, $attributeRepository);

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
            $attributes = $parentAttributes + $attributes;
        }

        return $attributes;
    }
}
