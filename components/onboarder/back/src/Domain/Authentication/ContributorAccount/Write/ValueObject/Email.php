<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\ValueObject;

final class Email
{
    private function __construct(private string $email)
    {
        if (false === \filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('The email must be valid.');
        }
    }

    public static function fromString(string $email): self
    {
        return new self($email);
    }

    public function __toString(): string
    {
        return $this->email;
    }

    public function equals(self $email): bool
    {
        return $this->email === (string) $email;
    }
}
