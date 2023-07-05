<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Pull-up master: do not pull this class: a migration handles the length's increase.
 */
final class IncreaseLabelLengthQuery
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

        $this->increaseLabelLength($databaseName);
    }

    private function increaseLabelLength(string $databaseName): void
    {
        $sql = <<< SQL
            SELECT CHARACTER_MAXIMUM_LENGTH 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = :database_name AND TABLE_NAME = 'oro_access_role' AND COLUMN_NAME = 'label';
        SQL;

        $scopeLength = $this->connection->executeQuery($sql, [
            'database_name' => $databaseName,
        ])->fetchOne();

        if ('255' === $scopeLength) {
            return;
        }

        $this->connection->executeQuery('ALTER TABLE oro_access_role MODIFY label VARCHAR(255) DEFAULT NULL');
    }
}
