<?php

namespace Akeneo\Pim\Structure\Bundle\Application\SwitchMainIdentifier;

class SwitchMainIdentifierCommand
{
    public function __construct(
        private string $newMainIdentifierCode,
    ) {
    }

    public function fromIdentifierCode(string $newMainIdentifierCode)
    {
        return new self($newMainIdentifierCode);
    }

    public function getNewMainIdentifierCode(): string
    {
        return $this->newMainIdentifierCode;
    }
}
