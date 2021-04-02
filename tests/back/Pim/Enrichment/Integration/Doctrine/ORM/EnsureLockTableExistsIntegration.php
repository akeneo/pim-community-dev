<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\ORM;

use Akeneo\Pim\Enrichment\Component\Lock\Query\EnsureLockTableExistsInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class EnsureLockTableExistsIntegration extends TestCase
{
    /** @var EnsureLockTableExistsInterface */
    private $query;

    /** @var Connection */
    private $connection;

    public function testItCreatesTheLockTableIfNotExists(): void
    {
        $this->dropTable();
        $this->query->execute();

        $sql = <<<SQL
    SELECT *
    FROM information_schema.tables
    WHERE table_name = 'lock_keys';
SQL;
        $result = $this->connection->executeQuery($sql);

        Assert::assertNotEmpty($result->fetch());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get('akeneo.pim.enrichment.query.ensure_lock_table_exists');
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function dropTable(): void
    {
        $sql = <<<SQL
DROP TABLE IF EXISTS lock_keys;
SQL;
        $this->connection->exec($sql);
    }
}
