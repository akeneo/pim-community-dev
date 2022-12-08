<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Webhook\EventsApiDebug\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\SystemClock;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\WebhookLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DownloadEventSubscriptionLogsEndToEnd extends WebTestCase
{
    private WebhookLoader $webhookLoader;
    private Client $elasticsearchClient;
    private FakeClock $clock;

    public function test_it_gets_file_of_event_subscription_logs(): void
    {
        $now = $this->clock->now()->getTimestamp();
        $sapConnection = $this->getConnection();
        $this->webhookLoader->initWebhook($sapConnection->code());

        $this->insertLogs(
            [
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $now - 2,
                    'level' => 'warning',
                    'message' => 'Foo bar',
                    'connection_code' => $sapConnection->code(),
                    'context' => ['foo' => 'bar'],
                ],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'timestamp' => $now - 1,
                    'level' => 'warning',
                    'message' => 'Foo bar 2',
                    'connection_code' => $sapConnection->code(),
                    'context' => ['foo' => 'bar2'],
                ],
            ]
        );

        $this->authenticateAsAdmin();

        \ob_start();

        $this->client->request(
            'GET',
            '/rest/events-api-debug/download-event-subscription-logs',
            ['connection_code' => $sapConnection->code()]
        );

        $response = $this->client->getResponse();
        $content = \ob_get_contents();

        \ob_end_clean();

        $expectedContent = <<<EOF
2021/03/02 03:30:09 WARNING Foo bar {"foo":"bar"}
2021/03/02 03:30:10 WARNING Foo bar 2 {"foo":"bar2"}\n
EOF;
        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertInstanceOf(StreamedResponse::class, $response);
        Assert::assertEquals($expectedContent, $content);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->webhookLoader = $this->get('akeneo_connectivity.connection.fixtures.webhook_loader');
        $this->elasticsearchClient = $this->get('akeneo_connectivity.client.events_api_debug');
        $this->clock = $this->get(SystemClock::class);

        $this->clock->setNow(new \DateTimeImmutable('2021-03-02T04:30:11'));
    }

    private function getConnection(): ConnectionWithCredentials
    {
        return $this->createConnection('sap', 'SAP', FlowType::DATA_SOURCE, true);
    }

    private function insertLogs(array $logs): void
    {
        $this->elasticsearchClient->bulkIndexes($logs);
        $this->elasticsearchClient->refreshIndex();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
