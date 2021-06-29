<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\AuditErrorLoader;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query\PurgeAuditErrorQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use PHPUnit\Framework\Assert;

class DbalPurgeAuditErrorQueryIntegration extends TestCase
{
    /** @var AuditErrorLoader */
    private $auditErrorLoader;

    /** @var PurgeAuditErrorQuery */
    private $purge;

    /** @var Connection */
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auditErrorLoader = $this->get('akeneo_connectivity.connection.fixtures.audit_error_loader');
        $this->purge = $this->get('akeneo_connectivity_connection.persistence.query.purge_audit_error');
        $this->connection = $this->get('database_connection');
    }

    public function test_it_purges_audit_errors_saved_before_the_given_datetime()
    {
        $utc = new \DateTimeZone('UTC');
        $this->auditErrorLoader->insert(
            '10days',
            HourlyInterval::createFromDateTime(new \DateTimeImmutable('now - 10 days', $utc)),
            5,
            ErrorTypes::BUSINESS
        );
        $this->auditErrorLoader->insert(
            '9days',
            HourlyInterval::createFromDateTime(new \DateTimeImmutable('now - 8 days', $utc)),
            5,
            ErrorTypes::TECHNICAL
        );
        $this->auditErrorLoader->insert(
            'now',
            HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', $utc)),
            5,
            ErrorTypes::BUSINESS
        );

        $purged = $this->purge->execute(new \DateTimeImmutable('now - 9 days', $utc));
        Assert::assertEquals($purged, 1);

        $query = <<<SQL
SELECT connection_code FROM akeneo_connectivity_connection_audit_error ORDER BY error_datetime ASC;
SQL;
        $errors = $this->connection->executeQuery($query)->fetchAll(FetchMode::COLUMN);

        Assert::assertEquals('9days', $errors[0]);
        Assert::assertEquals('now', $errors[1]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
