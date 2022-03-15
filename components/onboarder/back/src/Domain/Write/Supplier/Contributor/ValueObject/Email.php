<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor\ValueObject;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor\Model\Contributor;

final class Email
{
    private function __construct(private string $email)
    {
        if ('' === $email) {
            throw new \InvalidArgumentException('The contributor email cannot be empty.');
        }

        if (false === \filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('The contributor email must be valid.');
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
}
