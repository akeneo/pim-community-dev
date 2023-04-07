<?php

namespace Akeneo\Category\Domain\ValueObject;

use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\ImageDataValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\Value;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @implements \IteratorAggregate<int, ValueCollection>
 *
 * @phpstan-import-type ImageData from ImageDataValue
 *
 * @phpstan-type NormalizedValue array{data: string|ImageData|null, channel: string|null, locale: string|null, attribute_code: string}
 */
final class ValueCollection implements \IteratorAggregate, \Countable
{
    /**
     * @param Value[] $values
     */
    private function __construct(private array $values)
    {
        assert::allIsInstanceOf($values, Value::class);
        $this->assertUniqueValue($values);
    }

    /**
     * @param Value[] $values
     */
    public static function fromArray(array $values): self
    {
        return new self($values);
    }

    /**
     * @param array<string, array{
     *     data: string|ImageData|null,
     *     type: string,
     *     channel: string|null,
     *     locale: string|null,
     *     attribute_code: string,
     * }> $values
     */
    public static function fromDatabase(array $values): self
    {
        // We keep this filter to manage uncleaned data from previous code version
        $values = array_filter($values, function ($valueKey) {
            return $valueKey !== 'attribute_codes';
        }, ARRAY_FILTER_USE_KEY);

        $newValues = [];
        foreach ($values as $value) {
            $newValues[] = AbstractValue::fromType($value);
        }

        return new self($newValues);
    }

    /**
     * Get a value by his composite key.
     */
    public function getValue(string $attributeCode, string $attributeUuid, ?string $channel, ?string $localeCode): ?Value
    {
        $filteredValue = array_filter(
            $this->getValues(),
            static function (Value $value) use ($localeCode, $channel, $attributeUuid, $attributeCode) {
                return (string) $value->getCode() === $attributeCode
                    && (string) $value->getUuid() === $attributeUuid
                    && $value->getChannel()?->getValue() === $channel
                    && $value->getLocale()?->getValue() === $localeCode;
            },
        );

        return !empty($filteredValue) ? reset($filteredValue) : null;
    }

    /**
     * @return Value[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Set a value in value collection. If value already exist, update it.
     */
    public function setValue(Value $value): ValueCollection
    {
        $isUpdated = false;
        foreach ($this->values as $key => $existingValue) {
            if ($value->getKeyWithChannelAndLocale() === $existingValue->getKeyWithChannelAndLocale()) {
                $this->values[$key] = $value;
                $isUpdated = true;
                break;
            }
        }

        if (!$isUpdated) {
            $this->values[] = $value;
        }

        return new self($this->values);
    }

    public function removeValue(Value $value): void
    {
        $this->values = array_filter($this->values, static function ($existingValue) use ($value) {
            return !(
                (string) $existingValue->getUuid() === (string) $value->getUuid() &&
                (string) $existingValue->getChannel() === (string) $value->getChannel() &&
                (string) $existingValue->getLocale() === (string) $value->getLocale()
            );
        });
    }

    /**
     * @return array<string, NormalizedValue>
     */
    public function normalize(): array
    {
        $normalizedValues = [];
        foreach ($this->values as $value) {
            $normalizedValues = [...$normalizedValues, ...$value->normalize()];
        }

        return $normalizedValues;
    }

    /**
     * @phpstan-ignore-next-line
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->values);
    }

    public function count(): int
    {
        return count($this->values);
    }

    /**
     * @param Value[] $values
     */
    private function assertUniqueValue(array $values): void
    {
        $uniqueCompositeKeys = [];
        foreach ($values as $value) {
            $valueCompositeKey = $value->getKeyWithChannelAndLocale();
            if (in_array($valueCompositeKey, $uniqueCompositeKeys)) {
                throw new \InvalidArgumentException(sprintf('Duplicate value for %s', $valueCompositeKey));
            }
            $uniqueCompositeKeys[] = $valueCompositeKey;
        }
    }
}
