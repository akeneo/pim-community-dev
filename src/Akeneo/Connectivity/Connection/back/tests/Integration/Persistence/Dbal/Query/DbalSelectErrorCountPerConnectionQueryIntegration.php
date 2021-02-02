<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\AuditErrorLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCountPerConnection;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectErrorCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectErrorCountPerConnectionQueryIntegration extends TestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var AuditErrorLoader */
    private $auditErrorLoader;

    /** @var SelectErrorCountPerConnectionQuery */
    private $selectErrorCountPerConnectionQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->auditErrorLoader = $this->get('akeneo_connectivity.connection.fixtures.audit_error_loader');
        $this->selectErrorCountPerConnectionQuery = $this->get('akeneo_connectivity_connection.persistence.query.select_error_count_per_connection');
    }

    public function test_it_gets_error_count_per_connection()
    {
        $this->connectionLoader->createConnection('sap', 'SAP', FlowType::DATA_SOURCE, true);
        $this->connectionLoader->createConnection('bynder', 'Bynder', FlowType::DATA_SOURCE, true);
        $this->connectionLoader->createConnection('ecommerce', 'Ecommerce', FlowType::DATA_DESTINATION, true);
        $this->connectionLoader->createConnection('no_error', 'No error', FlowType::DATA_SOURCE, true);
        $this->connectionLoader->createConnection('not_auditable', 'Not auditable', FlowType::DATA_SOURCE, false);

        $this->createHourlyErrorCounts([
            ['bynder', ErrorTypes::BUSINESS, '2020-01-01 23:00:00', 12], // ignored
            ['sap', ErrorTypes::BUSINESS, '2020-01-02 00:00:00', 10],
            ['bynder', ErrorTypes::BUSINESS, '2020-01-02 12:00:00', 8],
            ['sap', ErrorTypes::TECHNICAL, '2020-01-03 12:00:00', 5], // ignored
            ['sap', ErrorTypes::BUSINESS, '2020-01-03 23:00:00', 4],
            ['bynder', ErrorTypes::BUSINESS, '2020-01-04 00:00:00', 2], // ignored
        ]);

        $fromDateTime = new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC'));
        $upToDateTime = new \DateTimeImmutable('2020-01-04 00:00:00', new \DateTimeZone('UTC'));
        $result = $this->selectErrorCountPerConnectionQuery->execute(
            new ErrorType(ErrorTypes::BUSINESS),
            $fromDateTime,
            $upToDateTime,
        );

        $expectedResult = new ErrorCountPerConnection([
            new ErrorCount('sap', 14),
            new ErrorCount('bynder', 8),
            new ErrorCount('no_error', 0),
        ]);

        Assert::assertEqualsCanonicalizing($expectedResult, $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createHourlyErrorCounts(array $hourlyErrorCountsData): void
    {
        foreach ($hourlyErrorCountsData as [$connectionCode, $errorType, $dateTimeStr, $errorCount]) {
            $utcDateTime = (new \DateTimeImmutable($dateTimeStr, new \DateTimeZone('UTC')));

            $this->auditErrorLoader
                ->insert(
                    $connectionCode,
                    HourlyInterval::createFromDateTime($utcDateTime),
                    $errorCount,
                    $errorType
                );
        }
    }
}
