<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Infrastructure\Authentication\ContributorAccount\Repository\Sql;

use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
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
                'id' => $contributorAccount->identifier(),
                'email' => $contributorAccount->email(),
                'access_token' => $contributorAccount->accessToken(),
                'access_token_created_at' => $contributorAccount->accessTokenCreatedAt(),
                'created_at' => $contributorAccount->createdAt(),
            ],
        );
    }
}
