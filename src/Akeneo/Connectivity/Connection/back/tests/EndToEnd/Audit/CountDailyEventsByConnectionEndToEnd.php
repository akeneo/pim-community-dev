<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Audit;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\AuditLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\AllConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\WeeklyEventCounts;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByConnectionEndToEnd extends WebTestCase
{
    public function test_it_finds_connections_event_by_created_product()
    {
        $this->createConnection('bynder', 'Bynder', FlowType::DATA_SOURCE);
        $this->createConnection('sap', 'SAP', FlowType::DATA_SOURCE);

        $hourlyEventCountsPerConnection = $this->createHourlyEventCountsPerConnection(
            ['bynder', 'sap'],
            (new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('Asia/Tokyo')))
                ->setTimezone(new \DateTimeZone('UTC')),
            (new \DateTimeImmutable('2020-01-09 00:00:00', new \DateTimeZone('Asia/Tokyo')))
                ->setTimezone(new \DateTimeZone('UTC')),
        );
        $weeklyEventCountsPerConnection = $this->hourlyToWeeklyEventCounts(
            '2020-01-01',
            '2020-01-08',
            'Asia/Tokyo',
            $hourlyEventCountsPerConnection
        );

        $user = $this->authenticateAsAdmin();
        $user->setTimezone('Asia/Tokyo');
        $this->get('pim_user.saver.user')->save($user);

        $this->client->request(
            'GET',
            '/rest/connections/audit/source-connections-event',
            [
                'event_type' => 'product_created',
                'end_date' => '2020-01-08'
            ],
        );
        $result = json_decode($this->client->getResponse()->getContent(), true);
        $expectedResult = array_reduce(
            $weeklyEventCountsPerConnection,
            function (array $data, WeeklyEventCounts $weeklyEventCounts) {
                return array_merge($data, $weeklyEventCounts->normalize());
            },
            []
        );

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    private function createHourlyEventCountsPerConnection(
        array $connectionCodes,
        \DateTimeImmutable $startDateTime,
        \DateTimeImmutable $endDateTime
    ): array {
        $period = new \DatePeriod($startDateTime, new \DateInterval('PT1H'), $endDateTime);
        $hourlyEventCountsPerConnection = [];

        foreach ($period as $dateTime) {
            $allCount = 0;

            foreach ($connectionCodes as $connectionCode) {
                $count = rand(0, 5);
                $allCount += $count;

                $hourlyEventCountsPerConnection[$connectionCode][] = new HourlyEventCount(
                    $connectionCode,
                    HourlyInterval::createFromDateTime($dateTime),
                    $count,
                    EventTypes::PRODUCT_CREATED
                );
            }

            $hourlyEventCountsPerConnection[AllConnectionCode::CODE][] = new HourlyEventCount(
                AllConnectionCode::CODE,
                HourlyInterval::createFromDateTime($dateTime),
                $allCount,
                EventTypes::PRODUCT_CREATED
            );
        }

        foreach (array_merge($connectionCodes, [AllConnectionCode::CODE]) as $connectionCode) {
            foreach ($hourlyEventCountsPerConnection[$connectionCode] as $hourlyEventCounts) {
                $this->getAuditLoader()->insert($hourlyEventCounts);
            }
        }

        return $hourlyEventCountsPerConnection;
    }

    private function hourlyToWeeklyEventCounts(
        string $startDate,
        string $endDate,
        string $timezone,
        array $hourlyEventCountsPerConnection
    ): array {
        $weeklyEventCountsPerConnection = [];

        foreach ($hourlyEventCountsPerConnection as $connectionCode => $hourlyEventCounts) {
            $weeklyEventCountsPerConnection[] = new WeeklyEventCounts(
                $connectionCode,
                $startDate,
                $endDate,
                $timezone,
                array_map(
                    function (HourlyEventCount $hourlyEventCount) {
                        return [$hourlyEventCount->hourlyInterval()->fromDateTime(), $hourlyEventCount->eventCount()];
                    },
                    $hourlyEventCounts
                )
            );
        }

        return $weeklyEventCountsPerConnection;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getAuditLoader(): AuditLoader
    {
        return $this->get('akeneo_connectivity.connection.fixtures.audit_loader');
    }
}
