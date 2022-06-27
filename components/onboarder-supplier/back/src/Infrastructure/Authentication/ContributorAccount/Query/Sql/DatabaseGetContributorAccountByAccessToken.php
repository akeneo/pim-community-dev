<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Query\Sql;

use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Read\GetContributorAccountByAccessToken;
use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Read\Model\ContributorAccount;
use Doctrine\DBAL\Connection;

final class DatabaseGetContributorAccountByAccessToken implements GetContributorAccountByAccessToken
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $accessToken): ?ContributorAccount
    {
        $sql = <<<SQL
            SELECT id, 
                   email,
                   access_token, 
                   access_token_created_at
            FROM akeneo_onboarder_serenity_contributor_account
            WHERE access_token = :accessToken
        SQL;

        $result = $this->connection->executeQuery(
            $sql,
            [
                'accessToken' => $accessToken,
            ],
        )->fetchAssociative();

        if (false === $result) {
            return null;
        }

        return new ContributorAccount(
            $result['id'],
            $result['email'],
            $result['access_token'],
            $result['access_token_created_at'],
        );
    }
}
