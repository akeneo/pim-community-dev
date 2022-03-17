<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor\Model;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor\ValueObject;
use JetBrains\PhpStorm\ArrayShape;

final class Contributor
{
    private ValueObject\Identifier $identifier;
    private ValueObject\Email $email;

    private function __construct(string $identifier, string $email)
    {
        $this->identifier = ValueObject\Identifier::fromString($identifier);
        $this->email = ValueObject\Email::fromString($email);
    }

    public static function create(string $identifier, string $email): self
    {
        return new self($identifier, $email);
    }

    public function identifier(): string
    {
        return (string) $this->identifier;
    }

    public function email(): string
    {
        return (string) $this->email;
    }

    #[ArrayShape(['identifier' => 'string', 'email' => 'string'])]
    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier(),
            'email' => $this->email()
        ];
    }
}
