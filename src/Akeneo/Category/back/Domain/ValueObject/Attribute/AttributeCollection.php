<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute;

use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeCollection
{
    /**
     * @param array<Attribute> $attributes
     */
    private function __construct(private ?array $attributes)
    {
        Assert::allIsInstanceOf($attributes, Attribute::class);
    }

    /**
     * @param array<Attribute> $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self($attributes);
    }

    /**
     * @return array<Attribute>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Retrieve an Attribute by his identifier.
     *
     * @param string $identifier format expected : 'code|uuid' (example : title|69e251b3-b876-48b5-9c09-92f54bfb528d)
     */
    public function getAttributeByIdentifier(string $identifier): ?Attribute
    {
        $attribute = array_filter(
            $this->attributes,
            static function ($attribute) use ($identifier) {
                return $attribute->getIdentifier() === $identifier;
            },
        );
        if (empty($attribute) || count($attribute) > 1) {
            return null;
        }

        return reset($attribute);
    }

    /**
     * Retrieve an Attribute by his code.
     */
    public function getAttributeByCode(string $code): ?Attribute
    {
        $attribute = array_filter(
            $this->attributes,
            static function ($attribute) use ($code) {
                return (string) $attribute->getCode() === $code;
            },
        );
        if (empty($attribute) || count($attribute) > 1) {
            return null;
        }

        return reset($attribute);
    }

    public function addAttribute(Attribute $attribute): self
    {
        $this->attributes[] = $attribute;

        return new self($this->attributes);
    }

    /**
     * @return array<int, mixed>
     */
    public function normalize(): array
    {
        return array_map(
            static fn (Attribute $attribute) => $attribute->normalize(),
            $this->attributes,
        );
    }
}
