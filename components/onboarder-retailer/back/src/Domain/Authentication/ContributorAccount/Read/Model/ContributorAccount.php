<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Read\Model;

final class ContributorAccount
{
    public function __construct(public string $id, public string $email, public string $accessToken, public bool $isAccessTokenValid)
    {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'accessToken' => $this->accessToken,
            'isAccessTokenValid' => $this->isAccessTokenValid,
        ];
    }
}
