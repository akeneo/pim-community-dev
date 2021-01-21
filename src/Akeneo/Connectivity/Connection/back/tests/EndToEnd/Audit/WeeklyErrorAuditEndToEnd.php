<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Audit;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\AuditErrorLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class WeeklyErrorAuditEndToEnd extends WebTestCase
{
    public function test_it_get_the_weekly_error_audit(): void
    {
        $this->createConnection('erp_1', 'ERP', FlowType::DATA_SOURCE, true);
        $this->createConnection('erp_2', 'ERP', FlowType::DATA_SOURCE, true);

        $this->createErrorCounts([
            ['erp_1', 'business', '2020-01-02 23:00:00', 1], // Ignored, before the period
            ['erp_2', 'technical', '2020-01-03 00:00:00', 2],
            ['erp_1', 'technical', '2020-01-03 00:00:00', 2],
            ['erp_1', 'business', '2020-01-04 00:00:00', 3],
            ['erp_1', 'technical', '2020-01-05 00:00:00', 4],
            ['erp_2', 'business', '2020-01-06 00:00:00', 5],
            ['erp_1', 'business', '2020-01-06 00:00:00', 5],
            ['erp_1', 'technical', '2020-01-06 00:00:00', 55],
            ['erp_1', 'technical', '2020-01-07 00:00:00', 6],
            ['erp_1', 'business', '2020-01-08 00:00:00', 7],
            ['erp_1', 'technical', '2020-01-09 00:00:00', 8],
            ['erp_2', 'technical', '2020-01-09 00:00:00', 8],
            ['erp_1', 'business', '2020-01-10 00:00:00', 9],
            ['erp_1', 'business', '2020-01-10 23:00:00', 99],
            ['erp_1', 'technical', '2020-01-11 00:00:00', 10], // Ignored, after the period
        ], 'Asia/Tokyo');

        $expectedResult = [
            "<all>" =>  [
                "previous_week" =>  [
                    "2020-01-03" => 4
                ],
                "current_week" => [
                    "2020-01-04" => 3,
                    "2020-01-05" => 4,
                    "2020-01-06" => 65,
                    "2020-01-07" => 6,
                    "2020-01-08" => 7,
                    "2020-01-09" => 16,
                    "2020-01-10" => 108
                ],
                "current_week_total" => 209
            ],
            "erp_1" =>  [
                "previous_week" =>  [
                    "2020-01-03" => 2
                ],
                "current_week" =>  [
                    "2020-01-04" => 3,
                    "2020-01-05" => 4,
                    "2020-01-06" => 60,
                    "2020-01-07" => 6,
                    "2020-01-08" => 7,
                    "2020-01-09" => 8,
                    "2020-01-10" => 108
                ],
                "current_week_total" => 196
            ],
            "erp_2" =>  [
                "previous_week" =>  [
                    "2020-01-03" => 2
                ],
                "current_week" =>  [
                    "2020-01-04" => 0,
                    "2020-01-05" => 0,
                    "2020-01-06" => 5,
                    "2020-01-07" => 0,
                    "2020-01-08" => 0,
                    "2020-01-09" => 8,
                    "2020-01-10" => 0
                ],
                "current_week_total" => 13
            ]
        ];

        $user = $this->authenticateAsAdmin();
        $user->setTimezone('Asia/Tokyo');
        $this->get('pim_user.saver.user')->save($user);

        $this->client->request(
            'GET',
            '/rest/connections/audit/weekly-error',
            [
                'end_date' => '2020-01-10'
            ],
        );
        $result = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getAuditErrorLoader(): AuditErrorLoader
    {
        return $this->get('akeneo_connectivity.connection.fixtures.audit_error_loader');
    }

    private function createErrorCounts(array $hourlyErrorCountData, string $userDateTimeZoneStr): void
    {
        foreach ($hourlyErrorCountData as [$connectionCode, $errorType, $userDateTimeStr, $errorCount]) {
            $utcDateTime = (new \DateTimeImmutable($userDateTimeStr, new \DateTimeZone($userDateTimeZoneStr)))
                ->setTimezone(new \DateTimeZone('UTC'));

            $this->getAuditErrorLoader()
                ->insert(
                    $connectionCode,
                    HourlyInterval::createFromDateTime($utcDateTime),
                    $errorCount,
                    $errorType
                );
        }
    }
}
