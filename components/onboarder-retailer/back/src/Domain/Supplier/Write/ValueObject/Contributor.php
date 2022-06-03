<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Contributor\Email;

final class Contributor
{
    private Email $email;

    private function __construct(string $email)
    {
        $this->email = Email::fromString($email);
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
