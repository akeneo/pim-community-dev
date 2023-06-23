<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence\Query;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query\IncreaseLabelLengthQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Pull-up master: do not pull this class: a migration handles the length's increase.
 */
class IncreaseLabelLengthQueryIntegration extends TestCase
{
    private IncreaseLabelLengthQuery $query;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->get(IncreaseLabelLengthQuery::class);
        $this->connection = $this->get('database_connection');
    }

    public function test_it_updates_label_column_length(): void
    {
        if ('30' !== $this->getAccessRoleLabelLength()) {
            $this->resetDefaultScopeColumnsLength();
        }

        $this->assertEquals('30', $this->getAccessRoleLabelLength());

        $this->query->execute();

        $this->assertEquals('255', $this->getAccessRoleLabelLength());
    }

    private function getAccessRoleLabelLength(): string
    {
        $databaseNameSql = 'SELECT database()';
        $databaseName = $this->connection->executeQuery($databaseNameSql)->fetchOne();

        $sql = <<< SQL
            SELECT CHARACTER_MAXIMUM_LENGTH 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = :database_name AND TABLE_NAME = 'oro_access_role' AND COLUMN_NAME = 'label';
        SQL;

        $accessTokenScopeLength = $this->connection->executeQuery($sql, [
            'database_name' => $databaseName,
        ])->fetchOne();

        return $accessTokenScopeLength;
    }

    private function resetDefaultScopeColumnsLength(): void
    {
        $this->connection->executeQuery('ALTER TABLE oro_access_role MODIFY label VARCHAR(30) DEFAULT NULL');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
