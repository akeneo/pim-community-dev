<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\AuditLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\WeeklyEventCounts;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectConnectionsEventCountByDayQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectConnectionsEventCountByDayQueryIntegration extends TestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var AuditLoader */
    private $auditLoader;

    /** @var SelectConnectionsEventCountByDayQuery */
    private $selectConnectionsEventCountByDayQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->auditLoader = $this->get('akeneo_connectivity.connection.fixtures.audit_loader');
        $this->selectConnectionsEventCountByDayQuery = $this->get('akeneo_connectivity.connection.persistence.query.select_connections_event_count_by_day');
    }

    public function test_it_get_data_for_connections_with_audit_data()
    {
        $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_SOURCE);
        $this->connectionLoader->createConnection('bynder', 'Bynder', FlowType::DATA_SOURCE);

        $this->auditLoader->insertData('magento', new \DateTime('2020-01-01'), 5, EventTypes::PRODUCT_UPDATED);
        $this->auditLoader->insertData('magento', new \DateTime('2020-01-02'), 10, EventTypes::PRODUCT_UPDATED);
        $this->auditLoader->insertData('magento', new \DateTime('2020-01-07'), 4, EventTypes::PRODUCT_UPDATED);
        $this->auditLoader->insertData('magento', new \DateTime('2020-01-10'), 10, EventTypes::PRODUCT_UPDATED);
        $this->auditLoader->insertData('bynder', new \DateTime('2020-01-01'), 2, EventTypes::PRODUCT_UPDATED);
        $this->auditLoader->insertData('bynder', new \DateTime('2020-01-08'), 8, EventTypes::PRODUCT_UPDATED);
        $this->auditLoader->insertData('<all>', new \DateTime('2020-01-02'), 132, EventTypes::PRODUCT_UPDATED);
        $this->auditLoader->insertData('<all>', new \DateTime('2020-01-04'), 0, EventTypes::PRODUCT_UPDATED);

        $weeklyEventCountsPerConnection = $this->selectConnectionsEventCountByDayQuery->execute(
            EventTypes::PRODUCT_UPDATED,
            date('2020-01-01'),
            date('2020-01-08')
        );

        $result = array_reduce(
            $weeklyEventCountsPerConnection,
            function (array $data, WeeklyEventCounts $connectionEventCounts) {
                return array_merge($data, $connectionEventCounts->normalize());
            },
            []
        );

        $expectedResult = [
            'bynder' => [
                "2020-01-01" => 2,
                "2020-01-02" => 0,
                "2020-01-03" => 0,
                "2020-01-04" => 0,
                "2020-01-05" => 0,
                "2020-01-06" => 0,
                "2020-01-07" => 0,
                "2020-01-08" => 8,
            ],
            'magento' => [
                "2020-01-01" => 5,
                "2020-01-02" => 10,
                "2020-01-03" => 0,
                "2020-01-04" => 0,
                "2020-01-05" => 0,
                "2020-01-06" => 0,
                "2020-01-07" => 4,
                "2020-01-08" => 0,
            ],
            '<all>' => [
                "2020-01-01" => 0,
                "2020-01-02" => 132,
                "2020-01-03" => 0,
                "2020-01-04" => 0,
                "2020-01-05" => 0,
                "2020-01-06" => 0,
                "2020-01-07" => 0,
                "2020-01-08" => 0
            ]
        ];

        Assert::assertContainsOnlyInstancesOf(WeeklyEventCounts::class, $weeklyEventCountsPerConnection);
        Assert::assertSame($expectedResult, $result);
    }

    public function test_it_get_data_for_connections_without_audit_data()
    {
        $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_SOURCE);

        $weeklyEventCountsPerConnection = $this->selectConnectionsEventCountByDayQuery->execute(
            EventTypes::PRODUCT_UPDATED,
            date('2020-01-01'),
            date('2020-01-08')
        );

        $result = array_reduce(
            $weeklyEventCountsPerConnection,
            function (array $data, WeeklyEventCounts $connectionEventCounts) {
                return array_merge($data, $connectionEventCounts->normalize());
            },
            []
        );

        $expectedResult = [
            'magento' => [
                "2020-01-01" => 0,
                "2020-01-02" => 0,
                "2020-01-03" => 0,
                "2020-01-04" => 0,
                "2020-01-05" => 0,
                "2020-01-06" => 0,
                "2020-01-07" => 0,
                "2020-01-08" => 0,
            ],
            '<all>' => [
                "2020-01-01" => 0,
                "2020-01-02" => 0,
                "2020-01-03" => 0,
                "2020-01-04" => 0,
                "2020-01-05" => 0,
                "2020-01-06" => 0,
                "2020-01-07" => 0,
                "2020-01-08" => 0
            ]
        ];

        Assert::assertContainsOnlyInstancesOf(WeeklyEventCounts::class, $weeklyEventCountsPerConnection);
        Assert::assertSame($expectedResult, $result);
    }

        protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
