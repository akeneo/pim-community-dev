<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Query\Sql;

use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Read\GetContributorAccountByEmail;
use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Doctrine\DBAL\Connection;

final class DatabaseGetContributorAccountByEmail implements GetContributorAccountByEmail
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $email): ?ContributorAccount
    {
        $sql = <<<SQL
            SELECT id, email, created_at, password, access_token, access_token_created_at, last_logged_at
            FROM akeneo_onboarder_serenity_contributor_account
            WHERE email = :email
        SQL;

        $result = $this
            ->connection
            ->executeQuery($sql, ['email' => $email])
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
