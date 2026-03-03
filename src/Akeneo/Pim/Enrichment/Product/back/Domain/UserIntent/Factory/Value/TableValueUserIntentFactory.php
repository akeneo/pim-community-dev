<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTableValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\ValidateDataTrait;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\ValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TableValueUserIntentFactory implements ValueUserIntentFactory
{
    use ValidateDataTrait;

    public function getSupportedAttributeTypes(): array
    {
        return [AttributeTypes::TABLE];
    }

    public function create(string $attributeType, string $attributeCode, mixed $data): ValueUserIntent
    {
        $this->validateValueStructure($attributeCode, $data);

        if (null === $data['data']) {
            return new ClearValue($attributeCode, $data['scope'], $data['locale']);
        }

        if (!\is_array($data['data'])) {
            throw InvalidPropertyTypeException::arrayExpected($attributeCode, static::class, $data['data']);
        }

        return new SetTableValue($attributeCode, $data['scope'], $data['locale'], $data['data']);
    }
}
