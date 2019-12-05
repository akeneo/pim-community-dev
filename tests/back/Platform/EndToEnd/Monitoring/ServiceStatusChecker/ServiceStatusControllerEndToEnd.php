<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\EndToEnd\Monitoring\ServiceStatusChecker;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ElasticsearchChecker;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ServiceStatus;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;

final class ServiceStatusControllerEndToEnd extends TestCase
{
    public function test_it_gets_status_services(): void
    {
        $client = static::$kernel->getContainer()->get('test.client');
        $client->request('GET', '/monitoring/services_status');
        $response = $client->getResponse();

        $expectedContent = [
            'service_status' => [
                'mysql' => [
                    'ok' => true,
                    'message' => 'OK'
                ],
                'elasticsearch' => [
                    'ok' => true,
                    'message' => 'OK'
                ],
                'file_storage' => [
                    'ok' => true,
                    'message' => 'OK'
                ]
            ]
        ];

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
