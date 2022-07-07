<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFileValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceDataValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
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
class StringValueUserIntentFactory implements ValueUserIntentFactory
{
    use ValidateDataTrait;

    public function getSupportedAttributeTypes(): array
    {
        return [
            AttributeTypes::TEXT,
            AttributeTypes::TEXTAREA,
            AttributeTypes::OPTION_SIMPLE_SELECT,
            AttributeTypes::FILE,
            AttributeTypes::IMAGE,
            AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT,
            AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT
        ];
    }

    public function create(string $attributeType, string $attributeCode, mixed $data): ValueUserIntent
    {
        $this->validateValueStructure($attributeCode, $data);
        if (null === $data['data'] || '' === $data['data']) {
            return new ClearValue($attributeCode, $data['scope'], $data['locale']);
        }
        if (!\is_string($data['data'])) {
            throw InvalidPropertyTypeException::stringExpected($attributeCode, static::class, $data['data']);
        }

        return match ($attributeType) {
            AttributeTypes::TEXT => new SetTextValue($attributeCode, $data['scope'], $data['locale'], $data['data']),
            AttributeTypes::TEXTAREA => new SetTextareaValue($attributeCode, $data['scope'], $data['locale'], $data['data']),
            AttributeTypes::OPTION_SIMPLE_SELECT => new SetSimpleSelectValue($attributeCode, $data['scope'], $data['locale'], $data['data']),
            AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT => new SetSimpleReferenceEntityValue($attributeCode, $data['scope'], $data['locale'], $data['data']),
            AttributeTypes::FILE => new SetFileValue($attributeCode, $data['scope'], $data['locale'], $data['data']),
            AttributeTypes::IMAGE => new SetImageValue($attributeCode, $data['scope'], $data['locale'], $data['data']),
            AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT => new SetSimpleReferenceDataValue($attributeCode, $data['scope'], $data['locale'], $data['data']),
            default => throw new \InvalidArgumentException('Not implemented')
        };
    }
}
