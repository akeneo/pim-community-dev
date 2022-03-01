<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Supplier;

use Ramsey\Uuid\Uuid;

final class Identifier
{
    private function __construct(private string $identifier)
    {
        if ('' === $identifier) {
            throw new \InvalidArgumentException('The supplier identifier cannot be empty.');
        }

        if (!Uuid::isValid($identifier)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The supplier identifier must be a UUID, "%s" given.',
                    $identifier
                )
            );
        }
    }

    public static function fromString(string $identifier): self
    {
        return new self($identifier);
    }

    public function __toString(): string
    {
        return $this->identifier;
    }

    public function equals(self $other): bool
    {
        return (string) $other === (string) $this;
    }
}
