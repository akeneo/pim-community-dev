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

    public function addAttribute(Attribute $attribute): self
    {
        $this->attributes[] = $attribute;

        return new self($this->attributes);
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(): array
    {
        return array_map(
            static fn (Attribute $attribute) => $attribute->normalize(),
            $this->attributes,
        );
    }
}
