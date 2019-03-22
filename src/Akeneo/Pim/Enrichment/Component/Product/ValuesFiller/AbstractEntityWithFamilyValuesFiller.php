<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\ValuesFiller;

use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Sql\AttributeInterface;
use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Abstract values filler for entities with a family.
 *
 * As all entities with a family have ProductValues, there is a lot of common logic to
 * fill empty Product Values, this class is designed for that.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractEntityWithFamilyValuesFiller implements EntityWithFamilyValuesFillerInterface
{
    /** @var EntityWithValuesBuilderInterface */
    protected $entityWithValuesBuilder;

    /** @var AttributeValuesResolverInterface */
    protected $valuesResolver;

    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /** @var LruArrayAttributeRepository */
    protected $attributeRepository;

    public function __construct(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValuesResolverInterface $valuesResolver,
        CurrencyRepositoryInterface $currencyRepository,
        LruArrayAttributeRepository $attributeRepository
    ) {
        $this->entityWithValuesBuilder = $entityWithValuesBuilder;
        $this->valuesResolver = $valuesResolver;
        $this->currencyRepository = $currencyRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function fillMissingValues(EntityWithFamilyInterface $entity): void
    {
        $this->checkEntityType($entity);

        $attributes = $this->getExpectedAttributes($entity);
        $requiredValues = $this->valuesResolver->resolveEligibleValues($attributes);
        $existingValues = $this->getExistingValues($entity);

        $missingValues = array_filter(
            $requiredValues,
            function ($value) use ($existingValues) {
                return !in_array($value, $existingValues);
            }
        );

        foreach ($missingValues as $value) {
            $this->entityWithValuesBuilder->addOrReplaceValue(
                $entity,
                $attributes[$value['attribute']],
                $value['locale'],
                $value['scope'],
                null
            );
        }

        $this->addMissingPricesToProduct($entity);
    }

    /**
     * Returns an array of product values identifiers
     *
     * @param EntityWithValuesInterface $entity
     *
     * @return array
     */
    protected function getExistingValues(EntityWithValuesInterface $entity): array
    {
        $existingValues = [];
        $values = $entity->getValues();
        foreach ($values as $value) {
            $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());

            if (null !== $attribute) {
                $existingValues[] = [
                    'attribute' => $attribute->getCode(),
                    'type'      => $attribute->getType(),
                    'locale'    => $value->getLocaleCode(),
                    'scope'     => $value->getScopeCode()
                ];
            }
        }

        return $existingValues;
    }

    /**
     * Add missing prices (a price per currency)
     *
     * @param EntityWithValuesInterface $entity
     */
    protected function addMissingPricesToProduct(EntityWithValuesInterface $entity): void
    {
        $activeCurrencyCodes = $this->currencyRepository->getActivatedCurrencyCodes();

        foreach ($entity->getValues() as $value) {
            $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());
            if (null !== $attribute && AttributeTypes::PRICE_COLLECTION === $attribute->getType()) {
                $prices = [];

                foreach ($value->getData() as $price) {
                    if (in_array($price->getCurrency(), $activeCurrencyCodes)) {
                        $prices[] = ['amount' => $price->getData(), 'currency' => $price->getCurrency()];
                    }
                }

                foreach ($activeCurrencyCodes as $currencyCode) {
                    if (null === $value->getPrice($currencyCode)) {
                        $prices[] = ['amount' => null, 'currency' => $currencyCode];
                    }
                }

                $this->entityWithValuesBuilder->addOrReplaceValue($entity, $attribute, $value->getLocaleCode(), $value->getScopeCode(), $prices);
            }
        }
    }

    /**
     * Check the given $entity type, it can be a Product, a Product Model, a Variant Product...
     *
     * @throws \InvalidArgumentException if this Values Filler doesn't handle this kind of entity
     *
     * @param EntityWithFamilyInterface $entity
     */
    abstract protected function checkEntityType(EntityWithFamilyInterface $entity): void;

    /**
     * Get expected attributes for the given $entity
     *
     * @param EntityWithFamilyInterface $entity
     *
     * @return AttributeInterface[]
     */
    abstract protected function getExpectedAttributes(EntityWithFamilyInterface $entity): array;
}
