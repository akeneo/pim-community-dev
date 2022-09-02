<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Query\Sql;

use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Security\ContributorAccount;
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
            FROM akeneo_supplier_portal_contributor_account
            WHERE email = :email and password IS NOT NULL
        SQL;

        $result = $this
            ->connection
            ->executeQuery($sql, ['email' => $email])
            ->fetchAssociative()
        ;

        return false !== $result ? new ContributorAccount($result['email'], $result['password']) : null;
    }
}
