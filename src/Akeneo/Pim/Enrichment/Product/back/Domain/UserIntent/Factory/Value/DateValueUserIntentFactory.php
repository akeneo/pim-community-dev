<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\ValidateDataTrait;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\ValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateValueUserIntentFactory implements ValueUserIntentFactory
{
    use ValidateDataTrait;
    private const PATTERN = '/^(?<year>\d{4})-(?<month>\d{2})-(?<day>\d{2})(T(\d{2}):(\d{2}):(\d{2}(?:\.\d*)?)(([-+](\d{2}):(\d{2})|Z)?))?$/';

    public function getSupportedAttributeTypes(): array
    {
        return [AttributeTypes::DATE];
    }

    public function create(string $attributeType, string $attributeCode, mixed $data): ValueUserIntent
    {
        $this->validateValueStructure($attributeCode, $data);
        if (null === $data['data'] || '' === $data['data']) {
            return new ClearValue($attributeCode, $data['scope'], $data['locale']);
        }
        if (!is_string($data['data'])) {
            throw InvalidPropertyTypeException::stringExpected($attributeCode, static::class, $data['data']);
        }
        if (!preg_match(self::PATTERN, $data['data'], $matches)) {
            throw InvalidPropertyException::dateExpected($attributeCode, 'yyyy-mm-dd', static::class, $data['data']);
        }
        if (!\checkdate((int) $matches['month'], (int) $matches['day'], (int) $matches['year'])) {
            throw InvalidPropertyException::dateExpected($attributeCode, 'yyyy-mm-dd', static::class, $data['data']);
        }

        $formattedDate = \sprintf('%d-%d-%d', $matches['year'], $matches['month'], $matches['day']);
        $dateTimeValue = \DateTimeImmutable::createFromFormat('Y-m-d', $formattedDate);
        if (false === $dateTimeValue) {
            throw InvalidPropertyException::dateExpected($attributeCode, 'yyyy-mm-dd', static::class, $data['data']);
        }

        return new SetDateValue($attributeCode, $data['scope'], $data['locale'], $dateTimeValue);
    }
}
