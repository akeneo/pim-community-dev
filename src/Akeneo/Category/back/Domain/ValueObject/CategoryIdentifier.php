<?php

namespace Akeneo\Category\Domain\ValueObject;

use Webmozart\Assert\Assert;

class CategoryIdentifier implements \Stringable
{
    public function __construct(
        private int $identifier
    ) {
    }

    public static function fromString(string $identifier)
    {
        Assert::numeric($identifier);
        return new self((int) $identifier);
    }

    public function __toString(): string
    {
        return (string) $this->identifier;
    }
}
