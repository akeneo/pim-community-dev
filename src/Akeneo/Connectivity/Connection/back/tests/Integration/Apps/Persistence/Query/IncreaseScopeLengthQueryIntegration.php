<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence\Query;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query\IncreaseScopeLengthQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Pull-up master: do not pull this class: a migration handles the length's increase.
 */
class IncreaseScopeLengthQueryIntegration extends TestCase
{
    private IncreaseScopeLengthQuery $query;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->get(IncreaseScopeLengthQuery::class);
        $this->connection = $this->get('database_connection');
    }

    public function test_it_updates_scope_column_length(): void
    {
        if ('255' !== $this->getAccessTokenScopeLength()) {
            $this->resetDefaultScopeColumnsLength();
        }

        $this->assertEquals('255', $this->getAccessTokenScopeLength());
        $this->assertEquals('255', $this->getRefreshTokenScopeLength());
        $this->assertEquals('255', $this->getAuthCodeScopeLength());

        $this->query->execute();

        $this->assertEquals('1000', $this->getAccessTokenScopeLength());
        $this->assertEquals('1000', $this->getRefreshTokenScopeLength());
        $this->assertEquals('1000', $this->getAuthCodeScopeLength());
    }

    private function getAccessTokenScopeLength(): string
    {
        $databaseNameSql = 'SELECT database()';
        $databaseName = $this->connection->executeQuery($databaseNameSql)->fetchOne();

        $findAccessTokenScopeLengthSql = <<< SQL
        SELECT CHARACTER_MAXIMUM_LENGTH 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = :database_name AND TABLE_NAME = 'pim_api_access_token' AND COLUMN_NAME = 'scope';
        SQL;

        $accessTokenScopeLength = $this->connection->executeQuery($findAccessTokenScopeLengthSql, [
            'database_name' => $databaseName,
        ])->fetchOne();

        return $accessTokenScopeLength;
    }

    private function getRefreshTokenScopeLength(): string
    {
        $databaseNameSql = 'SELECT database()';
        $databaseName = $this->connection->executeQuery($databaseNameSql)->fetchOne();

        $findRefreshTokenScopeLengthSql = <<< SQL
        SELECT CHARACTER_MAXIMUM_LENGTH 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = :database_name AND TABLE_NAME = 'pim_api_refresh_token' AND COLUMN_NAME = 'scope';
        SQL;

        $refreshTokenScopeLength = $this->connection->executeQuery($findRefreshTokenScopeLengthSql, [
            'database_name' => $databaseName,
        ])->fetchOne();

        return $refreshTokenScopeLength;
    }

    private function getAuthCodeScopeLength(): string
    {
        $databaseNameSql = 'SELECT database()';
        $databaseName = $this->connection->executeQuery($databaseNameSql)->fetchOne();

        $findAuthCodeScopeLengthSql = <<< SQL
        SELECT CHARACTER_MAXIMUM_LENGTH 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = :database_name AND TABLE_NAME = 'pim_api_auth_code' AND COLUMN_NAME = 'scope';
        SQL;

        $authCodeScopeLength = $this->connection->executeQuery($findAuthCodeScopeLengthSql, [
            'database_name' => $databaseName,
        ])->fetchOne();

        return $authCodeScopeLength;
    }

    private function resetDefaultScopeColumnsLength(): void
    {
        $this->connection->executeQuery('ALTER TABLE pim_api_access_token MODIFY scope VARCHAR(255) DEFAULT NULL');
        $this->connection->executeQuery('ALTER TABLE pim_api_refresh_token MODIFY scope VARCHAR(255) DEFAULT NULL');
        $this->connection->executeQuery('ALTER TABLE pim_api_auth_code MODIFY scope VARCHAR(255) DEFAULT NULL');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
