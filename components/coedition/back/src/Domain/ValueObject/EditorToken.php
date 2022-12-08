<?php

namespace Akeneo\CoEdition\Domain\ValueObject;

final class EditorToken implements \Stringable
{

    private function __construct(
        private string $token
    )
    {

    }

    public static function fromString(string $token): self
    {
        return new self($token);
    }

    public function __toString(): string
    {
        return $this->token;
    }
}
