<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Pull-up master: do not pull this class: a migration handles the length's increase.
 */
final class IncreaseScopeLengthQuery
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function execute(): void
    {
        $databaseNameSql = 'SELECT database()';
        $databaseName = $this->connection->executeQuery($databaseNameSql)->fetchOne();
        if (!is_string($databaseName)) {
            return;
        }

        $this->increaseAccessTokenScopeLength($databaseName);
        $this->increaseRefreshTokenScopeLength($databaseName);
        $this->increaseAuthCodeScopeLength($databaseName);
    }

    private function increaseAccessTokenScopeLength(string $databaseName): void
    {
        $findAccessTokenScopeLengthSql = <<< SQL
        SELECT CHARACTER_MAXIMUM_LENGTH 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = :database_name AND TABLE_NAME = 'pim_api_access_token' AND COLUMN_NAME = 'scope';
        SQL;

        $scopeLength = $this->connection->executeQuery($findAccessTokenScopeLengthSql, [
            'database_name' => $databaseName,
        ])->fetchOne();

        if ('1000' === $scopeLength) {
            return;
        }

        $this->connection->executeQuery('ALTER TABLE pim_api_access_token MODIFY scope VARCHAR(1000) DEFAULT NULL');
    }

    private function increaseRefreshTokenScopeLength(string $databaseName): void
    {
        $findRefreshTokenScopeLengthSql = <<< SQL
        SELECT CHARACTER_MAXIMUM_LENGTH 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = :database_name AND TABLE_NAME = 'pim_api_refresh_token' AND COLUMN_NAME = 'scope';
        SQL;

        $scopeLength = $this->connection->executeQuery($findRefreshTokenScopeLengthSql, [
            'database_name' => $databaseName,
        ])->fetchOne();

        if ('1000' === $scopeLength) {
            return;
        }

        $this->connection->executeQuery('ALTER TABLE pim_api_refresh_token MODIFY scope VARCHAR(1000) DEFAULT NULL');
    }

    private function increaseAuthCodeScopeLength(string $databaseName): void
    {
        $findAuthCodeScopeLengthSql = <<< SQL
        SELECT CHARACTER_MAXIMUM_LENGTH 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = :database_name AND TABLE_NAME = 'pim_api_auth_code' AND COLUMN_NAME = 'scope';
        SQL;

        $scopeLength = $this->connection->executeQuery($findAuthCodeScopeLengthSql, [
            'database_name' => $databaseName,
        ])->fetchOne();

        if ('1000' === $scopeLength) {
            return;
        }

        $this->connection->executeQuery('ALTER TABLE pim_api_auth_code MODIFY scope VARCHAR(1000) DEFAULT NULL');
    }
}
