<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Integration\Infrastructure\Authentication\ContributorAccount\Query\Sql;

use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Query\Sql\DatabaseGetContributorAccountByEmail;
use Akeneo\SupplierPortal\Supplier\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetContributorAccountByEmailIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itGetsAContributorAccountFromAnEmail(): void
    {
        $this->insertContributorAccount('9f4c017c-7682-4f83-9099-dd9afcada1a2', 'test@example.com');
        $this->insertContributorAccount('b8b13d0b-496b-4a7c-a574-0d522ba90752', 'burger@example.com');

        $contributorAccount = $this->get(DatabaseGetContributorAccountByEmail::class)('test@example.com');

        static::assertSame('test@example.com', $contributorAccount->getUserIdentifier());
        static::assertSame('foo', $contributorAccount->getPassword());
    }

    /** @test */
    public function itGetsNullIfEmailDoesNotExist(): void
    {
        $this->insertContributorAccount('9f4c017c-7682-4f83-9099-dd9afcada1a2', 'test@example.com');
        $contributorAccount = $this->get(DatabaseGetContributorAccountByEmail::class)('burger@example.com');

        static::assertNull($contributorAccount);
    }

    /** @test */
    public function itGetsNullIfAUserExitsWithNoPassword(): void
    {
        $this->insertContributorAccount('9f4c017c-7682-4f83-9099-dd9afcada1a2', 'test@example.com', null);
        $contributorAccount = $this->get(DatabaseGetContributorAccountByEmail::class)('test@example.com');

        static::assertNull($contributorAccount);
    }

    private function insertContributorAccount(string $id, string $email, ?string $password = 'foo'): void
    {
        $sql = <<<SQL
            INSERT INTO akeneo_supplier_portal_contributor_account (
                id, email, password, access_token, access_token_created_at, created_at
            ) VALUES (
                :id,
                :email,
                :password,
                'access-token',
                NOW(),
                NOW()
            )
        SQL;

        $this->connection->executeQuery($sql, [
            'id' => $id,
            'email' => $email,
            'password' => $password,
        ]);
    }
}
