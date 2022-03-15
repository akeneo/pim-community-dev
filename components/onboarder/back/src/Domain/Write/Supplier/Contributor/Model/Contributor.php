<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor\Model;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor\ValueObject;
use JetBrains\PhpStorm\ArrayShape;

final class Contributor
{
    private ValueObject\Email $email;

    private function __construct(string $email)
    {
        $this->email = ValueObject\Email::fromString($email);
    }

    public static function fromEmail(string $email): self
    {
        return new self($email);
    }

    public function email(): string
    {
        return (string) $this->email;
    }

    #[ArrayShape(['email' => 'string'])]
    public function toArray(): array
    {
        return [
            'email' => $this->email()
        ];
    }
}
