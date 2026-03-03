<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\ValidateDataTrait;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\ValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionValueUserIntentFactory implements ValueUserIntentFactory
{
    use ValidateDataTrait;

    public function getSupportedAttributeTypes(): array
    {
        return [AttributeTypes::PRICE_COLLECTION];
    }

    public function create(string $attributeType, string $attributeCode, mixed $data): ValueUserIntent
    {
        $priceValues = [];
        $this->validateValueStructure($attributeCode, $data);
        if (null === $data['data'] || [] === $data['data']) {
            return new ClearValue($attributeCode, $data['scope'], $data['locale']);
        }
        if (!\is_array($data['data'])) {
            throw InvalidPropertyTypeException::arrayExpected($attributeCode, static::class, $data['data']);
        }
        foreach ($data['data'] as $price) {
            $this->validateScalarArray($attributeCode, $price);
            if (!array_key_exists('amount', $price)) {
                throw InvalidPropertyTypeException::arrayKeyExpected($attributeCode, 'amount', static::class, $data);
            }
            if (!array_key_exists('currency', $price)) {
                throw InvalidPropertyTypeException::arrayKeyExpected($attributeCode, 'currency', static::class, $data);
            }
            if (null !== $price['amount'] && (!is_scalar($price['amount']) || \is_bool($price['amount']))) {
                throw InvalidPropertyTypeException::scalarExpected($attributeCode, 'amount', $price['amount']);
            }
            if (!is_string($price['currency'])) {
                throw InvalidPropertyTypeException::stringExpected($attributeCode, 'currency', $price['currency']);
            }
            if (null === $price['amount'] || '' === $price['amount']) {
                continue;
            }
            $priceValues[] = new PriceValue($price['amount'], $price['currency']);
        }

        if ([] === $priceValues) {
            return new ClearValue($attributeCode, $data['scope'], $data['locale']);
        }
        return new SetPriceCollectionValue($attributeCode, $data['scope'], $data['locale'], $priceValues);
    }
}
