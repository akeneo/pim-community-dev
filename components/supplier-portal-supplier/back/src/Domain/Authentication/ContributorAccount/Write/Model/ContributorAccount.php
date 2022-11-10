<?php

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Model;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\AccessToken;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Password;

class ContributorAccount
{
    private function __construct(
        private Identifier $identifier,
        private Email $email,
        private \DateTimeInterface $createdAt,
        private ?Password $password,
        private ?AccessToken $accessToken,
        private ?\DateTimeInterface $accessTokenCreatedAt,
        private ?\DateTimeInterface $lastLoggedAt,
        // Consent to Akeneo Privacy Policy / terms & conditions
        private bool $consent,
    ) {
    }

    public static function createdAtFromEmail(string $email, \DateTimeImmutable $createdAt): self
    {
        return new self(
            Identifier::generate(),
            Email::fromString($email),
            $createdAt,
            null,
            AccessToken::generate(),
            $createdAt,
            null,
            false,
        );
    }

    public static function hydrate(
        string $identifier,
        string $email,
        string $createdAt,
        ?string $password,
        ?string $accessToken,
        ?string $accessTokenCreatedAt,
        ?string $lastLoggedAt,
        bool $consent,
    ): self {
        return new self(
            Identifier::fromString($identifier),
            Email::fromString($email),
            new \DateTimeImmutable($createdAt),
            null !== $password ? Password::fromString($password) : null,
            null !== $accessToken ? AccessToken::fromString($accessToken) : null,
            null !== $accessTokenCreatedAt ? new \DateTimeImmutable($accessTokenCreatedAt) : null,
            null !== $lastLoggedAt ? new \DateTimeImmutable($lastLoggedAt) : null,
            $consent,
        );
    }

    public function setPassword(string $password): self
    {
        $this->password = Password::fromString($password);
        $this->accessToken = null;
        $this->accessTokenCreatedAt = null;

        return $this;
    }

    public function identifier(): string
    {
        return (string) $this->identifier;
    }

    public function email(): string
    {
        return (string) $this->email;
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

    public function getPassword(): ?string
    {
        return null === $this->password ? null : (string) $this->password;
    }

    public function resetPasswordAt(\DateTimeImmutable $resetAt): void
    {
        $this->password = null;
        $this->accessToken = AccessToken::generate();
        $this->accessTokenCreatedAt = $resetAt;
    }

    public function renewAccessTokenAt(\DateTimeImmutable $renewedAt): void
    {
        $this->accessToken = AccessToken::generate();
        $this->accessTokenCreatedAt = $renewedAt;
    }

    public function hasConsent(): bool
    {
        return $this->consent;
    }

    public function consent(): void
    {
        $this->consent = true;
    }
}
