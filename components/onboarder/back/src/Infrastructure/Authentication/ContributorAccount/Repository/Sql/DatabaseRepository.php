<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Authentication\ContributorAccount\Repository\Sql;

use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Doctrine\DBAL\Connection;

class DatabaseRepository implements ContributorAccountRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function save(ContributorAccount $contributorAccount): void
    {
        $sql = <<<SQL
            REPLACE INTO `akeneo_onboarder_serenity_contributor_account` (id, email, access_token, access_token_created_at, created_at)
            VALUES (:id, :email, :access_token, :access_token_created_at, :created_at)
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'id' => (string) $contributorAccount->identifier(),
                'email' => (string) $contributorAccount->email(),
                'access_token' => (string) $contributorAccount->accessToken(),
                'access_token_created_at' => null !== $contributorAccount->accessTokenCreatedAt() ? ($contributorAccount->accessTokenCreatedAt())->format('Y-m-d H:i:s') : null,
                'created_at' => ($contributorAccount->createdAt())->format('Y-m-d H:i:s'),
            ],
        );
    }
}
