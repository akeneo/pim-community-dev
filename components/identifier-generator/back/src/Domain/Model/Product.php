<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

final class Product
{
    public function __construct(
        private ?string $identifier
    ) {
    }

    public function identifier(): ?string
    {
        return $this->identifier;
    }
}
