<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Audit;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
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

        $this->loadAuditData('2020-01-08');

        $this->authenticateAsAdmin();
        $this->client->request(
            'GET',
            '/rest/connections/audit/source-connections-event',
            [
                'event_type' => 'product_created',
                'end_date' => '2020-01-08'
            ],
        );
        $result = json_decode($this->client->getResponse()->getContent(), true);

        $expectedResult = [
            '<all>' =>  [
                '2020-01-01' => 22,
                '2020-01-02' => 25,
                '2020-01-03' => 0,
                '2020-01-04' => 0,
                '2020-01-05' => 15,
                '2020-01-06' => 12,
                '2020-01-07' => 0,
                '2020-01-08' => 8,
            ],
            'franklin' => [
                '2020-01-01' => 0,
                '2020-01-02' => 25,
                '2020-01-03' => 0,
                '2020-01-04' => 0,
                '2020-01-05' => 20,
                '2020-01-06' => 12,
                '2020-01-07' => 0,
                '2020-01-08' => 6,
            ],
            'erp' =>  [
                '2020-01-01' => 22,
                '2020-01-02' => 0,
                '2020-01-03' => 0,
                '2020-01-04' => 0,
                '2020-01-05' => 5,
                '2020-01-06' => 0,
                '2020-01-07' => 0,
                '2020-01-08' => 2,
            ],
        ];

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    private function loadAuditData(string $endDate): void
    {
        $date = new \DateTimeImmutable($endDate, new \DateTimeZone('UTC'));

        $productCreatedEvents = [
            '<all>' => [
                [$date->modify('-8 day'), 666], // ignored
                [$date->modify('-7 day'), 22],
                [$date->modify('-6 day'), 25],
                [$date->modify('-3 day'), 15],
                [$date->modify('-2 day'), 12],
                [$date, 8],
            ],
            'erp' => [
                [$date->modify('-7 day'), 22],
                [$date->modify('-3 day'), 5],
                [$date, 2],
            ],
            'franklin' => [
                [$date->modify('-8 day'), 666], // ignored
                [$date->modify('-6 day'), 25],
                [$date->modify('-3 day'), 20],
                [$date->modify('-2 day'), 12],
                [$date, 6],
            ]
        ];

        $productUpdatedEvents = [
            '<all>' => [
                [$date, 16],
                [$date->modify('-2 day'), 24],
                [$date->modify('-5 day'), 28],
                [$date->modify('-8 day'), 666],
            ],
            'erp' => [
                [$date->modify('-5 day'), 28],
                [$date->modify('-2 day'), 24],
                [$date, 4],
            ],
            'franklin' => [
                [$date->modify('-8 day'), 666], // ignored
                [$date, 12],
            ]
        ];

        $auditLoader = $this->get('akeneo_connectivity.connection.fixtures.audit_loader');

        foreach ($productCreatedEvents as $code => $data) {
            foreach ($data as [$date, $count]) {
                $auditLoader->insertData($code, $date, $count, 'product_created');
            }
        }

        foreach ($productUpdatedEvents as $code => $data) {
            foreach ($data as [$date, $count]) {
                $auditLoader->insertData($code, $date, $count, 'product_updated');
            }
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
