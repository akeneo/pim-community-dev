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
            null,
        );
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPassword(): ?Password
    {
        return $this->password;
    }

    public function getAccessToken(): ?AccessToken
    {
        return $this->accessToken;
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
