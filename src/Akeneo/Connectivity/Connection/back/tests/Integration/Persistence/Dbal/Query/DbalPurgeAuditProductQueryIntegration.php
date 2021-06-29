<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\AuditLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\PurgeAuditProductQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class DbalPurgeAuditProductQueryIntegration extends TestCase
{
    /** @var AuditLoader */
    private $auditLoader;

    /** @var PurgeAuditProductQuery */
    private $purge;

    /** @var Connection */
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auditLoader = $this->get('akeneo_connectivity.connection.fixtures.audit_loader');
        $this->purge = $this->get('akeneo_connectivity_connection.persistence.query.purge_audit_product');
        $this->connection = $this->get('database_connection');
    }

    public function test_it_purges_audit_saved_before_the_given_datetime()
    {
        $utc = new \DateTimeZone('UTC');
        $this->auditLoader->insert(
            new HourlyEventCount(
                '10days',
                HourlyInterval::createFromDateTime(new \DateTimeImmutable('now - 10 days', $utc)),
                5,
                EventTypes::PRODUCT_UPDATED
            )
        );
        $this->auditLoader->insert(
            new HourlyEventCount(
                'now',
                HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', $utc)),
                5,
                EventTypes::PRODUCT_UPDATED
            )
        );

        $purged = $this->purge->execute(new \DateTimeImmutable('now - 5 days'));
        Assert::assertEquals($purged, 1);

        $query = <<<SQL
SELECT connection_code FROM akeneo_connectivity_connection_audit_product;
SQL;
        $connectionCode = $this->connection->executeQuery($query)->fetchColumn();
        Assert::assertEquals('now', $connectionCode);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
