<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
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
        if (null === $data['data'] || [] === $data['data']) {
            return new ClearValue($attributeCode, $data['scope'], $data['locale']);
        }
        $this->validateScalarArray($attributeCode, $data);

        $priceValues = [];
        foreach ($data as $measurementData) {

            foreach ($data['data'] as $measurement) {
                $this->validateValueStructure($attributeCode, $data);
                $this->validateScalarArray($attributeCode, $measurement);
                if (!array_key_exists('amount', $measurement)) {
                    throw InvalidPropertyTypeException::arrayKeyExpected($attributeCode, 'amount', static::class, $data);
                }
                if (!array_key_exists('currency', $measurement)) {
                    throw InvalidPropertyTypeException::arrayKeyExpected($attributeCode, 'currency', static::class, $data);
                }

                if (null === $data['data']['amount'] || '' === $data['data']['amount']) {
                    return new ClearValue($attributeCode, $data['scope'], $data['locale']);
                }

//                $userIntents[] = new SetTextValue($attributeCode, null, null, 'coucou');
            }
        }

        return new SetTextValue($attributeCode, null, null, 'coucou');
    }
}
