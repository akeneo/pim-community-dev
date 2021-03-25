<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Webhook;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\EventsApiDebugLogLevels;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\EventSubscriptionLogLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\WebhookLoader;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchEventSubscriptionLogsEndToEnd extends WebTestCase
{
    private WebhookLoader $webhookLoader;
    private FakeClock $clock;
    private EventSubscriptionLogLoader $eventSubscriptionLogLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webhookLoader = $this->get('akeneo_connectivity.connection.fixtures.webhook_loader');
        $this->clock = $this->get('akeneo_connectivity.connection.clock');
        $this->eventSubscriptionLogLoader = $this->get(
            'akeneo_connectivity.connection.fixtures.event_subscription_log_loader'
        );

        $this->clock->setNow(new \DateTimeImmutable('2021-03-02T04:30:11'));
    }

    public function test_it_gets_event_subscription_logs(): void
    {
        $timestamp = $this->clock->now()->getTimestamp();
        $sapConnection = $this->createConnection('sap', 'SAP', FlowType::DATA_SOURCE, true);
        $this->webhookLoader->initWebhook($sapConnection->code());
        $this->authenticateAsAdmin();

        $this->generateLogs(
            function () use ($timestamp) {
                return [
                    'timestamp' => $timestamp,
                    'level' => EventsApiDebugLogLevels::NOTICE,
                    'message' => 'Foo bar',
                    'connection_code' => null,
                    'context' => [],
                ];
            },
            30
        );

        $this->client->request(
            'GET',
            '/rest/events-api-debug/search-event-subscription-logs',
            [
                'connection_code' => $sapConnection->code(),
                'filters' => '{}',
            ]
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertCount(25, $response['results']);
        Assert::assertEquals(30, $response['total']);

        $searchAfter = $response['search_after'];

        $this->client->request(
            'GET',
            '/rest/events-api-debug/search-event-subscription-logs',
            ['connection_code' => $sapConnection->code(), 'search_after' => $searchAfter, 'filters' => '{}']
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertCount(5, $response['results']);
    }

    public function test_it_filters_on_event_subscription_logs(): void
    {
        $timestamp = $this->clock->now()->getTimestamp();
        $sapConnection = $this->createConnection('sap', 'SAP', FlowType::DATA_SOURCE, true);
        $this->webhookLoader->initWebhook($sapConnection->code());
        $this->authenticateAsAdmin();

        $this->insertLogs([
            [
                'timestamp' => $timestamp,
                'level' => EventsApiDebugLogLevels::NOTICE,
                'message' => 'Foo bar',
                'connection_code' => null,
                'context' => [],
            ],
            [
                'timestamp' => $timestamp,
                'level' => EventsApiDebugLogLevels::ERROR,
                'message' => 'Foo bar',
                'connection_code' => null,
                'context' => [],
            ],
        ]);

        $filters = [
            'levels' => [
                EventsApiDebugLogLevels::NOTICE,
            ],
        ];

        $this->client->request(
            'GET',
            '/rest/events-api-debug/search-event-subscription-logs',
            [
                'connection_code' => $sapConnection->code(),
                'filters' => json_encode($filters),
            ],
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertCount(1, $response['results']);
        Assert::assertEquals(1, $response['total']);
    }

    private function generateLogs(callable $generator, int $number): void
    {
        $this->eventSubscriptionLogLoader->bulkInsert(array_map($generator, range(0, $number - 1)));
    }

    private function insertLogs(array $logs): void
    {
        $this->eventSubscriptionLogLoader->bulkInsert($logs);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
