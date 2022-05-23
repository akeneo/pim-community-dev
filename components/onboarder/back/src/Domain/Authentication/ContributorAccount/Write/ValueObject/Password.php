<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\ValueObject;

final class Password
{
    private function __construct(private string $password)
    {
    }

    public static function fromString(string $password): self
    {
        return new self($password);
    }

    public function __toString(): string
    {
        return $this->password;
    }
}
