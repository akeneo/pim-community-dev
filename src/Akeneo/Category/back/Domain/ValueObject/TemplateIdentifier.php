<?php

namespace Akeneo\Category\Domain\ValueObject;

use Webmozart\Assert\Assert;

class TemplateIdentifier implements \Stringable
{
    public function __construct(
        private string $identifier
    ) {
    }

    public function __toString(): string
    {
        return $this->identifier;
    }

}
