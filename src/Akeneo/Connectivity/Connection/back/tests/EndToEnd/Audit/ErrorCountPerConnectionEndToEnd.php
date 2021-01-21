<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Audit;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\AuditErrorLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\AuditLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\AllConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ErrorCountPerConnectionEndToEnd extends WebTestCase
{
    public function test_it_get_error_count_per_connection()
    {
        $this->getConnectionLoader()->createConnection('sap', 'SAP', FlowType::DATA_SOURCE, true);
        $this->getConnectionLoader()->createConnection('bynder', 'Bynder', FlowType::DATA_SOURCE, true);

        $this->createHourlyErrorCounts([
            ['bynder', 'business', '2019-12-31 23:00:00', 1], // ignored date
            ['bynder', 'technical', '2020-01-01 12:00:00', 1], // ignored technical
            ['bynder', 'business', '2020-01-01 16:00:00', 3],
            ['bynder', 'business', '2020-01-04 15:00:00', 5],
            ['bynder', 'business', '2020-01-04 16:00:00', 7],
            ['bynder', 'technical', '2020-01-07 16:00:00', 7], // ignored technical
            ['bynder', 'business', '2020-01-08 00:00:00', 9], // ignored date
            ['sap', 'business', '2020-01-04 14:00:00', 10],
            ['sap', 'business', '2020-01-04 15:00:00', 30],
            ['sap', 'business', '2020-01-08 23:00:00', 50], // ignored date
            ['sap', 'technical', '2020-01-08 23:00:00', 20], // ignored technical
        ], 'Asia/Tokyo');

        $user = $this->authenticateAsAdmin();
        $user->setTimezone('Asia/Tokyo');
        $this->get('pim_user.saver.user')->save($user);

        $this->client->request(
            'GET',
            '/rest/connections/audit/error-count-per-connection',
            [
                'error_type' => 'business',
                'end_date' => '2020-01-07'
            ],
        );
        $result = json_decode($this->client->getResponse()->getContent(), true);

        $expectedResult = [
            'bynder' => 15,
            'sap' => 40,
        ];

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getConnectionLoader(): ConnectionLoader
    {
        return $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
    }

    private function getAuditErrorLoader(): AuditErrorLoader
    {
        return $this->get('akeneo_connectivity.connection.fixtures.audit_error_loader');
    }

    private function createHourlyErrorCounts(array $hourlyErrorCountData, string $userDateTimeZoneStr): void
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
