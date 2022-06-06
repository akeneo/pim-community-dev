<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Integration\Infrastructure\Authentication\ContributorAccount\Query\Sql;

use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Read\GetContributorAccountByAccessToken;
use Akeneo\OnboarderSerenity\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetContributorAccountByAccessTokenIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itGetsAContributorAccountFromAValidAccessToken(): void
    {
        $sql = <<<SQL
            INSERT INTO akeneo_onboarder_serenity_contributor_account (
                id, email, password, access_token, access_token_created_at, created_at
            ) VALUES (
                '9f4c017c-7682-4f83-9099-dd9afcada1a2',
                'test@test.test',
                'foo',
                'access-token',
                NOW(),
                NOW()
            )
        SQL;

        $this->connection->executeQuery($sql);

        $contributorAccount = $this->get(GetContributorAccountByAccessToken::class)('access-token');

        static::assertSame(
            [
                'id' => '9f4c017c-7682-4f83-9099-dd9afcada1a2',
                'accessToken' => 'access-token',
                'isAccessTokenValid' => true,
            ],
            $contributorAccount->toArray(),
        );
    }

    /** @test */
    public function itGetsAContributorAccountWithAnInvalidAccessToken(): void
    {
        $sql = <<<SQL
            INSERT INTO akeneo_onboarder_serenity_contributor_account (
                id, email, password, access_token, access_token_created_at, created_at
            ) VALUES (
                '9f4c017c-7682-4f83-9099-dd9afcada1a2',
                'test@test.test',
                'foo',
                'access-token',
                NOW()-INTERVAL 15 DAY,
                NOW()
            )
        SQL;

        $this->connection->executeQuery($sql);

        $contributorAccount = $this->get(GetContributorAccountByAccessToken::class)('access-token');

        static::assertSame(
            [
                'id' => '9f4c017c-7682-4f83-9099-dd9afcada1a2',
                'accessToken' => 'access-token',
                'isAccessTokenValid' => false,
            ],
            $contributorAccount->toArray(),
        );
    }

    /** @test */
    public function itGetsNullIfTheAccessTokenIsUnknown(): void
    {
        $contributorAccount = $this->get(GetContributorAccountByAccessToken::class)('access-token');

        static::assertNull($contributorAccount);
    }
}
