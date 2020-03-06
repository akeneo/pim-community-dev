<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Audit;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\AuditLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\HourlyInterval;
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
        $this->createConnection('franklin', 'Franklin', FlowType::DATA_SOURCE);
        $this->createConnection('erp', 'ERP', FlowType::DATA_SOURCE);

        // $this->createDailyEventCountsPerConnection(['erp', 'franklin'], '2019-12-28', 'Asia/Tokyo', 15);

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

        dd($result);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    private function createDailyEventCountsPerConnection(
        array $connectionCodes,
        string $startDate,
        string $timezone,
        int $numberOfDays = 8
    ): void {
        $dateTimeZ = new \DateTimeImmutable($startDate, new \DateTimeZone($timezone));

        $utcDateTime = $dateTimeZ->setTimezone(new \DateTimeZone('UTC'));
        $numberOfHours = $numberOfDays * 24;

        $data = array_reduce(
            $connectionCodes,
            function (array $data, string $connectionCode) use ($utcDateTime, $numberOfHours) {
                $utcHours = [];

                for ($hour = 0; $hour < $numberOfHours; $hour++) {
                    $utcHours[] = $utcDateTime->add(new \DateInterval('PT' . $hour . 'H'));
                }

                $data[$connectionCode] = array_map(
                    function (\DateTimeImmutable $utcHour) {
                        return [$utcHour, rand(0, 100)];
                    },
                    $utcHours
                );

                return $data;
            },
            []
        );

        foreach ($data as $connectionCode => $utcHours) {
            foreach ($utcHours as [$utcHour, $count]) {
                $this->getAuditLoader()->insert(
                    new HourlyEventCount(
                        $connectionCode,
                        HourlyInterval::createFromDateTime($utcHour),
                        $count,
                        EventTypes::PRODUCT_CREATED
                    )
                );
            }
        }
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
