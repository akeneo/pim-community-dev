<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait ValidateDataTrait
{
    protected function validateScalarArray(string $fieldName, mixed $data): void
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($fieldName, static::class, $data);
        }

        foreach ($data as $value) {
            if (null !== $value && !is_scalar($value)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $fieldName,
                    sprintf('one of the %s is not a scalar', $fieldName),
                    static::class,
                    $data
                );
            }
        }
    }

    protected function validateValueStructure(string $attributeCode, mixed $value): void
    {
        if (!is_array($value)) {
            throw InvalidPropertyTypeException::validArrayStructureExpected($attributeCode, 'one of the values is not an array', static::class, [$value]);
        }

        if (!array_key_exists('locale', $value)) {
            throw InvalidPropertyTypeException::arrayKeyExpected($attributeCode, 'locale', static::class, $value);
        }

        if (!array_key_exists('scope', $value)) {
            throw InvalidPropertyTypeException::arrayKeyExpected($attributeCode, 'scope', static::class, $value);
        }

        if (!array_key_exists('data', $value)) {
            throw InvalidPropertyTypeException::arrayKeyExpected($attributeCode, 'data', static::class, $value);
        }

        if (null !== $value['locale'] && !is_string($value['locale'])) {
            $message = 'Property "%s" expects a value with a string as locale, "%s" given.';

            throw new InvalidPropertyTypeException(
                $attributeCode,
                $value['locale'],
                static::class,
                sprintf($message, $attributeCode, gettype($value['locale'])),
                InvalidPropertyTypeException::STRING_EXPECTED_CODE
            );
        }

        if (null !== $value['scope'] && !is_string($value['scope'])) {
            $message = 'Property "%s" expects a value with a string as scope, "%s" given.';

            throw new InvalidPropertyTypeException(
                $attributeCode,
                $value['scope'],
                static::class,
                sprintf($message, $attributeCode, gettype($value['scope'])),
                InvalidPropertyTypeException::STRING_EXPECTED_CODE
            );
        }
    }
}
