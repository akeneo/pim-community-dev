<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject;

class AccessToken
{
    private function __construct(private string $token)
    {
    }

    public static function generate(): self
    {
        return new self(
            base_convert(bin2hex(hash('sha256', uniqid((string) mt_rand(), true), true)), 16, 36),
        );
    }

    public static function fromString(string $accessToken): self
    {
        return new self($accessToken);
    }

    public function __toString(): string
    {
        return $this->token;
    }
}
