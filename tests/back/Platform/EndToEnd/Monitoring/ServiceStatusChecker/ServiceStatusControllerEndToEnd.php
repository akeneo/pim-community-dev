<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\EndToEnd\Monitoring\ServiceStatusChecker;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ElasticsearchChecker;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ServiceStatus;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class ServiceStatusControllerEndToEnd extends TestCase
{
    public function test_it_gets_status_services(): void
    {
        $client = static::$kernel->getContainer()->get('test.client');
        $token = $this->getParameter('monitoring_authentication_token');

        $client->request('GET', '/monitoring/services_status', [], [], ['HTTP_X-AUTH-TOKEN' => $token]);
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
                ],
                'smtp' => [
                    'ok' => true,
                    'message' => 'OK'
                ],
                'pub_sub' => [
                    'ok' => true,
                    'message' => 'OK'
                ]
            ]
        ];

        Assert::assertSame($expectedContent, json_decode($response->getContent(), true));
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function test_it_fails_to_get_statuses_with_bad_authentication(): void
    {
        $client = static::$kernel->getContainer()->get('test.client');
        $client->request('GET', '/monitoring/services_status');
        $response = $client->getResponse();

        Assert::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
