<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Infrastructure\Authentication\ContributorAccount\Query\Sql;

use Akeneo\SupplierPortal\Infrastructure\Authentication\ContributorAccount\Security\ContributorAccount;
use Doctrine\DBAL\Connection;

class DatabaseGetContributorAccountByEmail
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $email): ?ContributorAccount
    {
        $sql = <<<SQL
            SELECT email, password
            FROM akeneo_onboarder_serenity_contributor_account
            WHERE email = :email
        SQL;

        $result = $this
            ->connection
            ->executeQuery($sql, ['email' => $email])
            ->fetchAssociative()
        ;

        return false !== $result ? new ContributorAccount($result['email'], $result['password']) : null;
    }
}
