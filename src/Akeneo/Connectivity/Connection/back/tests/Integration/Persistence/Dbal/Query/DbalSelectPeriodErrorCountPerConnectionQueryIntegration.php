<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\AuditErrorLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectPeriodErrorCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectPeriodErrorCountPerConnectionQueryIntegration extends TestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var AuditErrorLoader */
    private $auditErrorLoader;

    /** @var SelectPeriodErrorCountPerConnectionQuery */
    private $selectPeriodErrorCountPerConnectionQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->auditErrorLoader = $this->get('akeneo_connectivity.connection.fixtures.audit_error_loader');

        $this->selectPeriodErrorCountPerConnectionQuery =
            $this->get('akeneo_connectivity_connection.persistence.query.select_period_error_count_per_connection');
    }

    public function test_it_gets_hourly_error_count_per_connection_for_a_given_period(): void
    {
        $this->connectionLoader->createConnection('erp_1', 'ERP', FlowType::DATA_SOURCE, true);
        $this->connectionLoader->createConnection('erp_2', 'ERP', FlowType::DATA_SOURCE, true);
        $this->connectionLoader->createConnection('erp_with_no_data', 'ERP', FlowType::DATA_SOURCE, true);
        $this->connectionLoader->createConnection('erp_not_auditable', 'ERP', FlowType::DATA_SOURCE, false);
        $this->connectionLoader->createConnection('ecommerce', 'Ecommerce', FlowType::DATA_DESTINATION, true);

        $this->createErrorCounts([
            ['erp_2', 'technical', '2019-12-31 23:00:00', 1], // Ignored, before the period
            ['erp_1', 'technical', '2020-01-01 00:00:00', 2],
            ['erp_not_auditable', 'business', '2020-01-01 00:00:00', 22], // Ignored, not auditable
            ['erp_1', 'business', '2020-01-02 23:00:00', 3],
            ['ecommerce', 'business', '2020-01-02 23:00:00', 33], // Ignored, not data_source
            ['erp_2', 'business', '2020-01-02 23:00:00', 4],
            ['erp_2', 'technical', '2020-01-02 23:00:00', 44],
            ['erp_1', 'technical', '2020-01-03 00:00:00', 5], // Ignored, after the period
        ]);

        $period = new DateTimePeriod(
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-03 00:00:00', new \DateTimeZone('UTC'))
        );

        $expectedResult = [
            new PeriodEventCount('<all>', $period->start(), $period->end(), [
                new Read\HourlyEventCount(new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')), 2),
                new Read\HourlyEventCount(new \DateTimeImmutable('2020-01-02 23:00:00', new \DateTimeZone('UTC')), 51)
            ]),
            new PeriodEventCount('erp_1', $period->start(), $period->end(), [
                new Read\HourlyEventCount(new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')), 2),
                new Read\HourlyEventCount(new \DateTimeImmutable('2020-01-02 23:00:00', new \DateTimeZone('UTC')), 3)
            ]),
            new PeriodEventCount('erp_2', $period->start(), $period->end(), [
                new Read\HourlyEventCount(new \DateTimeImmutable('2020-01-02 23:00:00', new \DateTimeZone('UTC')), 48),
            ]),
            new PeriodEventCount('erp_with_no_data', $period->start(), $period->end(), []),
        ];

        $result = $this->selectPeriodErrorCountPerConnectionQuery->execute($period);

        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_handles_an_empty_dataset(): void
    {
        $period = new DateTimePeriod(
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-03 00:00:00', new \DateTimeZone('UTC'))
        );

        $expectedResult = [
            new PeriodEventCount('<all>', $period->start(), $period->end(), []),
        ];

        $result = $this->selectPeriodErrorCountPerConnectionQuery->execute($period);

        Assert::assertEquals($expectedResult, $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createErrorCounts(array $hourlyErrorCountData): void
    {
        foreach ($hourlyErrorCountData as [$connectionCode, $errorType, $dateTimeStr, $errorCount]) {
            $this->auditErrorLoader->insert(
                $connectionCode,
                HourlyInterval::createFromDateTime(
                    new \DateTimeImmutable($dateTimeStr, new \DateTimeZone('UTC'))
                ),
                $errorCount,
                $errorType
            );
        }
    }
}
