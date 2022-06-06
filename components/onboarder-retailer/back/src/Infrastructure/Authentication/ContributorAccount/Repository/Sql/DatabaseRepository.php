<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Infrastructure\Authentication\ContributorAccount\Repository\Sql;

use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\ContributorAccountRepository;
use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\ValueObject\Identifier;
use Doctrine\DBAL\Connection;

class DatabaseRepository implements ContributorAccountRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function save(ContributorAccount $contributorAccount): void
    {
        $sql = <<<SQL
            REPLACE INTO `akeneo_onboarder_serenity_contributor_account` (
                id,
                email,
                password,
                access_token,
                access_token_created_at,
                created_at
            )
            VALUES (:id, :email, :password, :access_token, :access_token_created_at, :created_at)
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'id' => $contributorAccount->identifier(),
                'email' => $contributorAccount->email(),
                'password' => $contributorAccount->password(),
                'access_token' => $contributorAccount->accessToken(),
                'access_token_created_at' => $contributorAccount->accessTokenCreatedAt(),
                'created_at' => $contributorAccount->createdAt(),
            ],
        );
    }

    public function find(Identifier $contributorAccountIdentifier): ?ContributorAccount
    {
        $sql = <<<SQL
            SELECT id, email, created_at, password, access_token, access_token_created_at, last_logged_at
            FROM akeneo_onboarder_serenity_contributor_account
            WHERE id = :identifier
        SQL;

        $result = $this
            ->connection
            ->executeQuery($sql, ['identifier' => $contributorAccountIdentifier])
            ->fetchAssociative()
        ;

        return false !== $result
            ? ContributorAccount::hydrate(
                $result['id'],
                $result['email'],
                $result['created_at'],
                $result['password'],
                $result['access_token'],
                $result['access_token_created_at'],
                $result['last_logged_at'],
            )
            : null
        ;
    }
}
