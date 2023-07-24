<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;

/**
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EntityWithValuesBuilder implements EntityWithValuesBuilderInterface
{
    /** @var AttributeValuesResolverInterface */
    protected $valuesResolver;

    /** @var ValueFactory */
    protected $productValueFactory;

    /** @var GetAttributes */
    protected $getAttributesQuery;

    /**
     * @param AttributeValuesResolverInterface $valuesResolver
     * @param ValueFactory $productValueFactory
     */
    public function __construct(
        AttributeValuesResolverInterface $valuesResolver,
        ValueFactory $productValueFactory,
        GetAttributes $getAttributesQuery
    ) {
        $this->valuesResolver = $valuesResolver;
        $this->productValueFactory = $productValueFactory;
        $this->getAttributesQuery = $getAttributesQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function addOrReplaceValue(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        ?string $localeCode,
        ?string $channelCode,
        $data
    ): ?ValueInterface {
        $attribute = $this->getAttributesQuery->forCode($attribute->getCode());
        $data = $this->filterEmptyPricesAndMeasurements($attribute->type(), $data);

        $formerValue = $entityWithValues->getValue($attribute->code(), $localeCode, $channelCode);
        $isFormerValueFilled = null !== $formerValue && '' !== $formerValue && [] !== $formerValue;
        $isNewValueFilled = null !== $data && !$this->isEmptyStringValue($data) && [] !== $data;

        if (!$isFormerValueFilled && $isNewValueFilled) {
            return $this->createValue($entityWithValues, $attribute, $localeCode, $channelCode, $data);
        }

        if ($isFormerValueFilled && $isNewValueFilled) {
            return $this->updateValue($entityWithValues, $attribute, $localeCode, $channelCode, $data);
        }

        if ($isFormerValueFilled && !$isNewValueFilled) {
            return $this->removeValue($entityWithValues, $attribute, $localeCode, $channelCode, $data);
        }

        return null;
    }

    private function isEmptyStringValue($value): bool
    {
        return is_string($value) && '' === trim($value);
    }

    /**
     * Filter empty data for price collection and measurement attributes
     * As their data is more complex than for other attribute types,
     * it's not as easy to figure out if the value is empty
     * However we only filter values that have the right format, because we want the
     * value factory to throw an exception otherwise
     */
    private function filterEmptyPricesAndMeasurements(string $attributeType, $data)
    {
        if (AttributeTypes::METRIC === $attributeType) {
            if (is_array($data) && array_key_exists('amount', $data) && null === $data['amount']) {
                return null;
            }
        }

        if (AttributeTypes::PRICE_COLLECTION === $attributeType) {
            if (is_array($data)) {
                foreach ($data as $index => $price) {
                    // if the "amount" key does not exist, we don't filter it to let the ValueFactory throw an exception
                    if (is_array($price) && array_key_exists('amount', $price) && null === $price['amount']) {
                        unset($data[$index]);
                    }
                }
            }
        }

        return $data;
    }

    private function createValue(
        EntityWithValuesInterface $entityWithValues,
        Attribute $attribute,
        ?string $localeCode,
        ?string $channelCode,
        $data
    ): ValueInterface {
        $newValue = $this->productValueFactory->createByCheckingData($attribute, $channelCode, $localeCode, $data);
        $entityWithValues->addValue($newValue);

        return $newValue;
    }

    private function updateValue(
        EntityWithValuesInterface $entityWithValues,
        Attribute $attribute,
        ?string $localeCode,
        ?string $channelCode,
        $data
    ): ValueInterface {
        $formerValue = $entityWithValues->getValue($attribute->code(), $localeCode, $channelCode);
        $updatedValue = $this->productValueFactory->createByCheckingData($attribute, $channelCode, $localeCode, $data);
        if (!$formerValue->isEqual($updatedValue)) {
            $entityWithValues->removeValue($formerValue)->addValue($updatedValue);
        }

        return $updatedValue;
    }

    private function removeValue(
        EntityWithValuesInterface $entityWithValues,
        Attribute $attribute,
        ?string $localeCode,
        ?string $channelCode,
        $data
    ): ?ValueInterface {
        $formerValue = $entityWithValues->getValue($attribute->code(), $localeCode, $channelCode);
        $entityWithValues->removeValue($formerValue);

        return null;
    }
}
