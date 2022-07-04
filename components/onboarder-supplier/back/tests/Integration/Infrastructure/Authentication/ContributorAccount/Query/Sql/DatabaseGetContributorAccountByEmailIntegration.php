<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Test\Integration\Infrastructure\Authentication\ContributorAccount\Query\Sql;

use Akeneo\SupplierPortal\Infrastructure\Authentication\ContributorAccount\Query\Sql\DatabaseGetContributorAccountByEmail;
use Akeneo\SupplierPortal\Test\Integration\SqlIntegrationTestCase;

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

    public function insertContributorAccount(string $id, string $email): void
    {
        $sql = <<<SQL
            INSERT INTO akeneo_onboarder_serenity_contributor_account (
                id, email, password, access_token, access_token_created_at, created_at
            ) VALUES (
                :id,
                :email,
                'foo',
                'access-token',
                NOW(),
                NOW()
            )
        SQL;

        $this->connection->executeQuery($sql, [
            'id' => $id,
            'email' => $email,
        ]);
    }
}
