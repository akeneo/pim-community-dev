<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject;

final class Contributor
{
    private Contributor\Email $email;

    private function __construct(string $email)
    {
        $this->email = Contributor\Email::fromString($email);
    }

    public static function fromEmail(string $email): self
    {
        return new self($email);
    }

    public function email(): string
    {
        return (string) $this->email;
    }
}
