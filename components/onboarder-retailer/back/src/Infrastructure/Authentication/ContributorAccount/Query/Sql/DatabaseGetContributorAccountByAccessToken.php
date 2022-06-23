<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Infrastructure\Authentication\ContributorAccount\Query\Sql;

use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Read\GetContributorAccountByAccessToken;
use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Read\Model\ContributorAccount;
use Doctrine\DBAL\Connection;

final class DatabaseGetContributorAccountByAccessToken implements GetContributorAccountByAccessToken
{
    private const TOKEN_VALIDITY_IN_DAYS = 14;

    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $accessToken): ?ContributorAccount
    {
        $sql = <<<SQL
            SELECT id, 
                   email,
                   access_token, 
                   access_token_created_at >= NOW()-INTERVAL :tokenValidityInDays DAY as is_valid_access_token
            FROM akeneo_onboarder_serenity_contributor_account
            WHERE access_token = :accessToken
        SQL;

        $result = $this->connection->executeQuery(
            $sql,
            [
                'accessToken' => $accessToken,
                'tokenValidityInDays' => self::TOKEN_VALIDITY_IN_DAYS,
            ],
        )->fetchAssociative();

        if (false === $result) {
            return null;
        }

        $isValidAccessToken = (bool) $result['is_valid_access_token'];

        return new ContributorAccount($result['id'], $result['email'], $result['access_token'], $isValidAccessToken);
    }
}
