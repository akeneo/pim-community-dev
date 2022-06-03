<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\ValueObject;

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

    public function __toString(): string
    {
        return $this->token;
    }
}
