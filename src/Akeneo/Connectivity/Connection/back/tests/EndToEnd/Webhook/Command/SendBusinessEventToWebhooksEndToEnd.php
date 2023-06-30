<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Webhook\Command;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\CommandTestCase;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\EventsApiDebugLogLevels;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SendBusinessEventToWebhooksEndToEnd extends CommandTestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_limits_webhooks_sent(): void
    {
        $eventsApiRequestCountLoader = $this->get('akeneo_connectivity.connection.fixtures.events_api_request_count_loader');
        $eventsApiRequestCountLoader->createEventsApiRequestCount(
            new \DateTimeImmutable('1800 seconds ago', new \DateTimeZone('UTC')), // 30 minutes ago
            4500 // the webhook_requests_limit is 4000
        );
        $denormalizedBulkEvent = $this->getDenormalizedBulkEvent();

        /** @var Command $command */
        $command = $this->application->find('akeneo:connectivity:send-business-event');
        $commandTester = new CommandTester($command);

        $commandCode = $commandTester->execute(['message' => \json_encode($denormalizedBulkEvent, JSON_THROW_ON_ERROR)]);

        $this->assertEquals(Command::SUCCESS, $commandCode);
        $this->assertWebhookLimitWarningLogIsFound();
    }

    public function test_it_does_not_limits_webhooks_sent(): void
    {
        $eventsApiRequestCountLoader = $this->get('akeneo_connectivity.connection.fixtures.events_api_request_count_loader');
        $eventsApiRequestCountLoader->createEventsApiRequestCount(
            new \DateTimeImmutable('1800 seconds ago', new \DateTimeZone('UTC')), // 30 minutes ago
            1000  // the webhook_requests_limit is 4000
        );
        $denormalizedBulkEvent = $this->getDenormalizedBulkEvent();

        /** @var Command $command */
        $command = $this->application->find('akeneo:connectivity:send-business-event');
        $commandTester = new CommandTester($command);

        $commandCode = $commandTester->execute(['message' => \json_encode($denormalizedBulkEvent, JSON_THROW_ON_ERROR)]);

        $this->assertEquals(Command::SUCCESS, $commandCode);
        $this->assertWebhookLimitWarningLogIsNotFound();
    }

    private function getDenormalizedBulkEvent(): array
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);

        $event = new class($author, ['data'], 0, 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba') extends Event {
            public function getName(): string
            {
                return 'event_name';
            }
        };

        return [
            [
                'type' => \get_class($event),
                'name' => 'event_name',
                'author' => 'julia',
                'author_type' => 'ui',
                'data' => ['data'],
                'timestamp' => 0,
                'uuid' => 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba',
            ]
        ];
    }

    private function assertWebhookLimitWarningLogIsFound(): void
    {
        /** @var Client $client */
        $client = $this->get('akeneo_connectivity.client.events_api_debug');

        $query = [
            'query' => ['bool' => ['must' => [
                ['terms' => ['level' => [EventsApiDebugLogLevels::WARNING]]],
                ['match' => ['message' => 'The maximum number of events sent per hour has been reached.']],
            ]]],
        ];

        $rows = $client->search($query);

        $foundMessage = $rows['hits']['hits'][0]['_source']['message'] ?? null;
        self::assertEquals('The maximum number of events sent per hour has been reached.', $foundMessage, json_encode($rows));
    }

    private function assertWebhookLimitWarningLogIsNotFound(): void
    {
        /** @var Client $client */
        $client = $this->get('akeneo_connectivity.client.events_api_debug');

        $query = [
            'query' => ['bool' => ['must' => [
                ['terms' => ['level' => [EventsApiDebugLogLevels::WARNING]]],
                ['match' => ['message' => 'The maximum number of events sent per hour has been reached.']],
            ]]],
        ];

        $rows = $client->search($query);

        $foundMessage = $rows['hits']['hits'][0]['_source']['message'] ?? null;
        self::assertEquals(null, $foundMessage, json_encode($rows));
    }
}
