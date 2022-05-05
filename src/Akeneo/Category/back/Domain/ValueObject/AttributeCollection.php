<?php

namespace Akeneo\Category\Domain\ValueObject;

class AttributeCollection
{
    public function __construct(
        private array $attributes
    )
    {
    }

    public function normalize(): array
    {
        return array_map(fn($attribute) => $attribute->normalize(), $this->attributes);
    }

}
