<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\ValidateDataTrait;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\ValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasurementValueUserIntentFactory implements ValueUserIntentFactory
{
    use ValidateDataTrait;

    public function getSupportedAttributeTypes(): array
    {
        return [AttributeTypes::METRIC];
    }

    public function create(string $attributeType, string $attributeCode, mixed $data): ValueUserIntent
    {
        $this->validateValueStructure($attributeCode, $data);

        if (null === $data['data'] || '' === $data['data']) {
            return new ClearValue($attributeCode, $data['scope'], $data['locale']);
        }
        if (!\is_array($data['data'])) {
            throw InvalidPropertyTypeException::arrayExpected($attributeCode, static::class, $data['data']);
        }
        if (!array_key_exists('amount', $data['data'])) {
            throw InvalidPropertyTypeException::arrayKeyExpected($attributeCode, 'amount', static::class, $data);
        }
        if (!array_key_exists('unit', $data['data'])) {
            throw InvalidPropertyTypeException::arrayKeyExpected($attributeCode, 'unit', static::class, $data);
        }
        if (!is_string($data['data']['unit'])) {
            throw InvalidPropertyTypeException::stringExpected($attributeCode, 'unit', $data['data']['unit']);
        }
        if (null !== $data['data']['amount'] && (!is_scalar($data['data']['amount']) || \is_bool($data['data']['amount']))) {
            throw InvalidPropertyTypeException::scalarExpected($attributeCode, 'amount', $data['data']['unit']);
        }
        if (null === $data['data']['amount'] || '' === $data['data']['amount']) {
            return new ClearValue($attributeCode, $data['scope'], $data['locale']);
        }

        return new SetMeasurementValue(
            $attributeCode,
            $data['scope'],
            $data['locale'],
            $data['data']['amount'],
            $data['data']['unit']
        );
    }
}
