<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetAssetValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceDataValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\ValidateDataTrait;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\ValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultiStringValueUserIntentFactory implements ValueUserIntentFactory
{
    use ValidateDataTrait;

    public function getSupportedAttributeTypes(): array
    {
        return [
            AttributeTypes::OPTION_MULTI_SELECT,
            AttributeTypes::REFERENCE_ENTITY_COLLECTION,
            AttributeTypes::REFERENCE_DATA_MULTI_SELECT,
            AttributeTypes::ASSET_COLLECTION,
        ];
    }

    public function create(string $attributeType, string $attributeCode, mixed $data): ValueUserIntent
    {
        $this->validateValueStructure($attributeCode, $data);
        if ([] === $data['data']) {
            return new ClearValue($attributeCode, $data['scope'], $data['locale']);
        }
        $this->validateScalarArray($attributeCode, $data['data']);

        return match ($attributeType) {
            AttributeTypes::OPTION_MULTI_SELECT => new SetMultiSelectValue($attributeCode, $data['scope'], $data['locale'], $data['data']),
            AttributeTypes::REFERENCE_ENTITY_COLLECTION => new SetMultiReferenceEntityValue($attributeCode, $data['scope'], $data['locale'], $data['data']),
            AttributeTypes::ASSET_COLLECTION => new SetAssetValue($attributeCode, $data['scope'], $data['locale'], $data['data']),
            AttributeTypes::REFERENCE_DATA_MULTI_SELECT => new SetMultiReferenceDataValue($attributeCode, $data['scope'], $data['locale'], $data['data']),
            default => throw new \InvalidArgumentException('Not implemented')
        };
    }
}
