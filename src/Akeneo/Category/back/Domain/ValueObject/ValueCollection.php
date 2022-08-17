<?php

namespace Akeneo\Category\Domain\ValueObject;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ValueCollection
{
    // @phpstan-ignore-next-line
    private function __construct(private ?array $values)
    {
    }

    // @phpstan-ignore-next-line
    public static function fromArray(array $values): self
    {
        return new self($values);
    }

    // @phpstan-ignore-next-line
    public function getAttributeTextData(string $attributeCode, string $attributeIdentifier, string $localeCode): ?array
    {
        return $this->values[sprintf('%s_%s_%s', $attributeCode, $attributeIdentifier, $localeCode)];
    }

    // @phpstan-ignore-next-line
    public function getAttributeData(string $attributeCode, string $attributeIdentifier): ?array
    {
        return $this->values[sprintf('%s_%s', $attributeCode, $attributeIdentifier)];
    }
}
