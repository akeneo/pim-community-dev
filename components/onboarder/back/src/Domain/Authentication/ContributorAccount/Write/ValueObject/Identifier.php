<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\ValueObject;

use Ramsey\Uuid\Uuid;

final class Identifier
{
    private function __construct(private string $identifier)
    {
        if (!Uuid::isValid($identifier)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The identifier must be a UUID, "%s" given.',
                    $identifier,
                ),
            );
        }
    }

    public static function fromString(string $identifier): self
    {
        return new self($identifier);
    }

    public static function generate(): self
    {
        return self::fromString(Uuid::uuid4()->toString());
    }

    public function __toString(): string
    {
        return $this->identifier;
    }
}
