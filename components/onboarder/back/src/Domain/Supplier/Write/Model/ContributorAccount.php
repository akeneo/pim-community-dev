<?php

namespace Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier;

class ContributorAccount
{
    private function __construct(
        private Identifier $identifier,
        private string $email,
        private ?string $password,
        private \DateTimeInterface $createdAt,
        private ?\DateTimeInterface $lastLoggedAt,
    ) {
    }

    public static function fromEmail(string $email): self
    {
        return new self(Identifier::generate(), $email, null, new \DateTimeImmutable(), null);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getLastLoggedAt(): ?\DateTimeInterface
    {
        return $this->lastLoggedAt;
    }
}