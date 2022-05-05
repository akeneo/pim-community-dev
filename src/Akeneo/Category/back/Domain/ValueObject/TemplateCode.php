<?php

namespace Akeneo\Category\Domain\ValueObject;

use Webmozart\Assert\Assert;

class TemplateCode implements \Stringable
{
    public function __construct(
        private string $code
    ) {
        Assert::regex($code, '/^[a-zA-Z0-9_]+$/');
        Assert::maxLength($code, 255);
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
