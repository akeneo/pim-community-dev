<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Read\Model;

final class ContributorAccount
{
    public function __construct(public string $id, public string $accessToken, public bool $isAccessTokenValid)
    {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'accessToken' => $this->accessToken,
            'isAccessTokenValid' => $this->isAccessTokenValid,
        ];
    }
}
