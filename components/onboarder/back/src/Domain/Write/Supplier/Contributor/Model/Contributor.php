<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor\Model;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor\ValueObject;

final class Contributor
{
    private ValueObject\Identifier $identifier;
    private ValueObject\Email $email;
    private Supplier\ValueObject\Identifier $supplierIdentifier;

    private function __construct(string $identifier, string $email, string $supplierIdentifier)
    {
        $this->identifier = ValueObject\Identifier::fromString($identifier);
        $this->email = ValueObject\Email::fromString($email);
        $this->supplierIdentifier = Supplier\ValueObject\Identifier::fromString($supplierIdentifier);
    }

    public static function create(string $identifier, string $email, string $supplierIdentifier): self
    {
        return new self($identifier, $email, $supplierIdentifier);
    }

    public function identifier(): string
    {
        return (string) $this->identifier;
    }

    public function email(): string
    {
        return (string) $this->email;
    }

    public function supplierIdentifier(): string
    {
        return (string) $this->supplierIdentifier;
    }
}
