<?php

namespace Akeneo\Category\Domain\ValueObject\Attribute;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @implements \IteratorAggregate<string, mixed>
 *
 * @phpstan-type AdditionalProperties array<string, mixed>
 */
final class AttributeAdditionalProperties implements \IteratorAggregate
{
    /**
     * @param AdditionalProperties $additionalProperties
     */
    private function __construct(private ?array $additionalProperties)
    {
        Assert::allString($additionalProperties);
        Assert::allStringNotEmpty(\array_keys($additionalProperties));
    }

    /**
     * @param AdditionalProperties $additionalProperties
     */
    public static function fromArray(array $additionalProperties): self
    {
        return new self($additionalProperties);
    }

    /**
     * @return AdditionalProperties
     */
    public function getAdditionalProperties(): array
    {
        return $this->additionalProperties;
    }

    public function getAdditionalProperty(string $propertyName): ?string
    {
        return $this->additionalProperties[$propertyName] ?? null;
    }

    public function setAdditionalProperty(string $propertyName, string $value): void
    {
        $this->additionalProperties[$propertyName] = $value;
    }

    public function hasAdditionalProperty(string $propertyName): bool
    {
        return array_key_exists($propertyName, $this->additionalProperties);
    }

    /**
     * @return \ArrayIterator<string, mixed>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->additionalProperties);
    }

    /**
     * @return AdditionalProperties
     */
    public function normalize(): array
    {
        return $this->additionalProperties ?? [];
    }
}
