<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Infrastructure\Authentication\ContributorAccount\Query\Sql;

use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Read\GetContributorAccountByAccessToken;
use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Read\Model\ContributorAccount;
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
            new \DateTimeImmutable($result['access_token_created_at']),
        );
    }
}
