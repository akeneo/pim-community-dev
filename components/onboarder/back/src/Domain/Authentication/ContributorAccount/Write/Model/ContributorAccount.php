<?php

namespace Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\Model;

use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\ValueObject\AccessToken;
use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;
use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\ValueObject\Password;

class ContributorAccount
{
    private function __construct(
        private Identifier $identifier,
        private Email $email,
        private ?Password $password,
        private ?AccessToken $accessToken,
        private ?\DateTimeInterface $accessTokenCreatedAt,
        private \DateTimeInterface $createdAt,
        private ?\DateTimeInterface $lastLoggedAt,
    ) {
    }

    public static function fromEmail(string $email): self
    {
        return new self(
            Identifier::generate(),
            Email::fromString($email),
            null,
            AccessToken::generate(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            null,
        );
    }

    public function identifier(): string
    {
        return (string) $this->identifier;
    }

    public function email(): string
    {
        return (string) $this->email;
    }

    public function password(): ?string
    {
        return null === $this->password ? null : (string) $this->password;
    }

    public function accessToken(): ?string
    {
        return null === $this->accessToken ? null : (string) $this->accessToken;
    }

    public function accessTokenCreatedAt(): ?string
    {
        return $this->accessTokenCreatedAt?->format('Y-m-d H:i:s');
    }

    public function createdAt(): string
    {
        return $this->createdAt->format('Y-m-d H:i:s');
    }

    public function lastLoggedAt(): ?string
    {
        return $this->lastLoggedAt?->format('Y-m-d H:i:s');
    }
}
