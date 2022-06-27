<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Read\Model;

final class ContributorAccount
{
    private const TOKEN_VALIDITY_IN_DAYS = 14;

    public function __construct(
        public string $id,
        public string $email,
        public string $accessToken,
        public \DateTimeImmutable $accessTokenCreatedAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'accessToken' => $this->accessToken,
            'isAccessTokenValid' => $this->isAccessTokenValid($this->accessTokenCreatedAt),
        ];
    }

    public function isAccessTokenValid(\DateTimeImmutable $now): bool
    {
        return $this->accessTokenCreatedAt >= $now->modify(
            sprintf('-%s days', self::TOKEN_VALIDITY_IN_DAYS),
        );
    }
}
