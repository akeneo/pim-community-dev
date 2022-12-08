<?php

namespace Akeneo\CoEdition\Domain\ValueObject;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class RoomUuid implements \Stringable
{
    private function __construct(
        private readonly UuidInterface $uuid
    )
    {

    }

    public static function fromString(string $uuid): self
    {
        return new self(Uuid::fromString($uuid));
    }

    public static function create(): self
    {
        return new self(Uuid::uuid4());
    }

    public function __toString(): string
    {
        return $this->uuid->toString();
    }

    public function toBytes(): string
    {
        return $this->uuid->getBytes();
    }
}
