<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\ValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierValueUserIntentFactory implements ValueUserIntentFactory
{
    public function getSupportedAttributeTypes(): array
    {
        return [AttributeTypes::IDENTIFIER];
    }

    public function create(string $attributeType, string $attributeCode, mixed $data): ValueUserIntent
    {
        if (!\is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($attributeCode, static::class, $data);
        }
        if (!\array_key_exists('data', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected($attributeCode, 'data', static::class, $data);
        }
        if (!\is_string($data['data']) && null !== $data['data']) {
            throw InvalidPropertyTypeException::stringExpected($attributeCode, static::class, $data['data']);
        }

        return new SetIdentifierValue($attributeCode, $data['data']);
    }
}
