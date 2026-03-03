<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Marketplace\Persistence;

use Akeneo\Connectivity\Connection\Domain\Marketplace\GetUserProfileQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence\GetUserProfileQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;

class GetUserProfileQueryIntegration extends TestCase
{
    private GetUserProfileQueryInterface $getUserProfileQuery;
    private DbalConnection $dbalConnection;

    public function test_to_get_a_user_profile(): void
    {
        $this->createUser('willy', 'developer');
        $profile = $this->getUserProfileQuery->execute('willy');
        Assert::assertSame('developer', $profile);
    }

    public function test_to__get_null_if_user_has_no_profile(): void
    {
        $this->createUser('willy');
        $profile = $this->getUserProfileQuery->execute('willy');
        Assert::assertNull($profile);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getUserProfileQuery = $this->get(GetUserProfileQuery::class);
        $this->dbalConnection = self::getContainer()->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function createUser(string $username, ?string $profile = null)
    {
        $localeId = $this->dbalConnection->fetchOne('SELECT id FROM pim_catalog_locale LIMIT 1');

        $sqlInsert = <<<SQL
            INSERT INTO oro_user
            (username, email, ui_locale_id, salt, password, createdAt, updatedAt, timezone, properties, profile) VALUES
            (:username, :email, :localeId, 'my_salt', 'my_password', '2019-09-09', '2019-09-09', 'UTC', '{}', :profile)
SQL;

        $this->dbalConnection->executeQuery(
            $sqlInsert,
            [
                'username' => $username,
                'email' => $username . '@test.com',
                'localeId' => $localeId,
                'profile' => $profile,
            ]
        );
    }
}
