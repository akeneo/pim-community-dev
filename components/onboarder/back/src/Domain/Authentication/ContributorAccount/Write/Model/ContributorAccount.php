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

    public function identifier(): Identifier
    {
        return $this->identifier;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function password(): ?Password
    {
        return $this->password;
    }

    public function accessToken(): ?AccessToken
    {
        return $this->accessToken;
    }

    public function accessTokenCreatedAt(): ?\DateTimeInterface
    {
        return $this->accessTokenCreatedAt;
    }

    public function createdAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function lastLoggedAt(): ?\DateTimeInterface
    {
        return $this->lastLoggedAt;
    }
}
