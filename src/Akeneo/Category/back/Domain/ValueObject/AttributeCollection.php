<?php

namespace Akeneo\Category\Domain\ValueObject;

use Akeneo\Category\Domain\Model\Attribute;
use Webmozart\Assert\Assert;

class AttributeCollection
{
    public function __construct(
        private array $attributes
    )
    {
        Assert::allImplementsInterface($this->attributes, Attribute::class);
    }

    public function normalize(): array
    {
        return array_map(fn($attribute) => $attribute->normalize(), $this->attributes);
    }


}
