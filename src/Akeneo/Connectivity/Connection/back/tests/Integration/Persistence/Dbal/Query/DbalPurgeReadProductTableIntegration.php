<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\PurgeReadProductTableQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class DbalPurgeReadProductTableIntegration extends TestCase
{
    /** @var PurgeReadProductTableQuery */
    private $purgeQuery;

    /** @var Connection */
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->purgeQuery = $this->get('akeneo_connectivity_connection.persistence.query.purge_read_product');
    }

    public function test_it_deletes_rows_before_the_given_date()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $lastWeek = new \DateTime('now - 7 day', new \DateTimeZone('UTC'));
        $tenBefore = new \DateTime('now - 10 day', new \DateTimeZone('UTC'));

        $insertQuery = <<<SQL
INSERT INTO akeneo_connectivity_connection_read_product (product_id, username, event_datetime)
VALUES
    (1, 'magento', :now),
    (3, 'magento', :tenBefore),
    (4, 'bynder', :tenBefore)
SQL;
        $this->connection->executeUpdate(
            $insertQuery,
            ['now' => $now->format('Y-m-d H:i:s'), 'tenBefore' => $tenBefore->format('Y-m-d H:i:s')]
        );

        $this->purgeQuery->execute($lastWeek);

        $selectQuery = <<<SQL
SELECT product_id, username, event_datetime
FROM akeneo_connectivity_connection_read_product
SQL;
        $result = $this->connection->executeQuery($selectQuery)->fetchAll();
        Assert::assertCount(1, $result);
        Assert::assertEquals('1', $result[0]['product_id']);
        Assert::assertEquals('magento', $result[0]['username']);
        Assert::assertEquals($now->format('Y-m-d H:i:s'), $result[0]['event_datetime']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
