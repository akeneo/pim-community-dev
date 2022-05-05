<?php

namespace Akeneo\Category\Domain\ValueObject;

use Webmozart\Assert\Assert;

class CategoryCode implements \Stringable
{
    public function __construct(
        string $code
    ) {
        Assert::alpha($code);
        Assert::maxLength($code, 255);
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
