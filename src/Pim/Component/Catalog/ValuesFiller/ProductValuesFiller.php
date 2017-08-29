<?php

namespace Pim\Component\Catalog\ValuesFiller;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface;
use Pim\Component\Catalog\Manager\AttributeValuesResolverInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductValuesFiller implements EntityWithFamilyValuesFillerInterface
{
    /** @var EntityWithValuesBuilderInterface */
    private $entityWithValuesBuilder;

    /** @var AttributeValuesResolverInterface */
    private $valuesResolver;

    /** @var CurrencyRepositoryInterface */
    private $currencyRepository;

    /**
     * @param EntityWithValuesBuilderInterface $entityWithValuesBuilder
     * @param AttributeValuesResolverInterface $valuesResolver
     * @param CurrencyRepositoryInterface      $currencyRepository
     */
    public function __construct(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValuesResolverInterface $valuesResolver,
        CurrencyRepositoryInterface $currencyRepository
    ) {
        $this->entityWithValuesBuilder = $entityWithValuesBuilder;
        $this->valuesResolver          = $valuesResolver;
        $this->currencyRepository      = $currencyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function fillMissingValues(EntityWithFamilyInterface $product): void
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf('%s expected, %s given', ProductInterface::class, get_class($product))
            );
        }

        $attributes = $this->getExpectedAttributes($product);
        $requiredValues = $this->valuesResolver->resolveEligibleValues($attributes);
        $existingValues = $this->getExistingValues($product);

        $missingValues = array_filter(
            $requiredValues,
            function ($value) use ($existingValues) {
                return !in_array($value, $existingValues);
            }
        );

        foreach ($missingValues as $value) {
            $this->entityWithValuesBuilder->addOrReplaceValue(
                $product,
                $attributes[$value['attribute']],
                $value['locale'],
                $value['scope'],
                null
            );
        }

        $this->addMissingPricesToProduct($product);
    }

    /**
     * Get expected attributes for the product
     *
     * @param ProductInterface $product
     *
     * @return AttributeInterface[]
     */
    private function getExpectedAttributes(ProductInterface $product): array
    {
        $attributes = [];

        // TODO: remove this when optional attributes are gone
        $productAttributes = $product->getAttributes();
        foreach ($productAttributes as $attribute) {
            $attributes[$attribute->getCode()] = $attribute;
        }

        $family = $product->getFamily();
        if (null !== $family) {
            foreach ($family->getAttributes() as $attribute) {
                $attributes[$attribute->getCode()] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Returns an array of product values identifiers
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    private function getExistingValues(ProductInterface $product): array
    {
        $existingValues = [];
        $values = $product->getValues();
        foreach ($values as $value) {
            $existingValues[] = [
                'attribute' => $value->getAttribute()->getCode(),
                'type'      => $value->getAttribute()->getType(),
                'locale'    => $value->getLocale(),
                'scope'     => $value->getScope()
            ];
        }

        return $existingValues;
    }

    /**
     * Add missing prices (a price per currency)
     *
     * @param ProductInterface $product the product
     */
    private function addMissingPricesToProduct(ProductInterface $product): void
    {
        $activeCurrencyCodes = $this->currencyRepository->getActivatedCurrencyCodes();

        foreach ($product->getValues() as $value) {
            $attribute = $value->getAttribute();
            if (AttributeTypes::PRICE_COLLECTION === $attribute->getType()) {
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

                $this->entityWithValuesBuilder->addOrReplaceValue($product, $attribute, $value->getLocale(), $value->getScope(), $prices);
            }
        }
    }
}
