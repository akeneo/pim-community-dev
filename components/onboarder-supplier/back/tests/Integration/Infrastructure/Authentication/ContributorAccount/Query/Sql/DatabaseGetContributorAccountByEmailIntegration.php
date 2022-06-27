<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Test\Integration\Infrastructure\Authentication\ContributorAccount\Query\Sql;

use Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Query\Sql\DatabaseGetContributorAccountByEmail;
use Akeneo\OnboarderSerenity\Supplier\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetContributorAccountByEmailIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itGetsAContributorAccountFromAnEmail(): void
    {
        $sql = <<<SQL
            INSERT INTO akeneo_onboarder_serenity_contributor_account (
                id, email, password, access_token, access_token_created_at, created_at
            ) VALUES (
                '9f4c017c-7682-4f83-9099-dd9afcada1a2',
                'test@example.com',
                'foo',
                'access-token',
                NOW(),
                NOW()
            )
        SQL;

        $this->connection->executeQuery($sql);

        $contributorAccount = $this->get(DatabaseGetContributorAccountByEmail::class)('test@example.com');

        static::assertSame('test@example.com', $contributorAccount->getUserIdentifier());
        static::assertSame('foo', $contributorAccount->getPassword());
    }

    /** @test */
    public function itGetsNullIfEmailDoesNotExist(): void
    {
        $contributorAccount = $this->get(DatabaseGetContributorAccountByEmail::class)('burger');

        static::assertNull($contributorAccount);
    }
}
