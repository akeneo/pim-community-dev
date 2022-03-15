<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook\EventsApiDebug\Persistence;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\EventsApiDebugLogLevels;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Persistence\PurgeEventsApiErrorLogsQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;

class PurgeEventsApiErrorLogsQueryIntegration extends TestCase
{
    private Client $esClient;
    private PurgeEventsApiErrorLogsQuery $purgeErrorLogsQuery;

    public function test_it_purges_error_logs_older_than_the_given_date()
    {
        $interval = new \DateInterval('PT1H');
        // We generate logs for each hour and we iterate 10 times
        $this->generateLogs(
            '2021-05-15 16:17:00',
            $interval,
            10,
            [
                EventsApiDebugLogLevels::ERROR,
                EventsApiDebugLogLevels::WARNING,
                EventsApiDebugLogLevels::NOTICE,
                EventsApiDebugLogLevels::INFO,
            ]
        );

        // We want to purge errors that are older than 2h
        $this->purgeErrorLogsQuery->execute(
            (new \DateTimeImmutable('2021-05-15 16:17:00', new \DateTimeZone('UTC')))
                ->sub(new \DateInterval('PT2H'))
        );
        $this->esClient->refreshIndex();
        $infoResults = $this->findDocumentsByLevel(EventsApiDebugLogLevels::INFO);
        $noticeResults = $this->findDocumentsByLevel(EventsApiDebugLogLevels::NOTICE);
        $errorResults = $this->findDocumentsByLevel(EventsApiDebugLogLevels::ERROR);
        $warnResults = $this->findDocumentsByLevel(EventsApiDebugLogLevels::WARNING);

        Assert::assertEquals(10, $infoResults['hits']['total']['value']);
        Assert::assertEquals(10, $noticeResults['hits']['total']['value']);
        Assert::assertEquals(3, $errorResults['hits']['total']['value']);
        Assert::assertEquals(3, $warnResults['hits']['total']['value']);

        $now = (new \DateTimeImmutable('2021-05-15 16:17:00', new \DateTimeZone('UTC')))->getTimestamp();
        $oneHourAgo = (new \DateTimeImmutable('2021-05-15 15:17:00', new \DateTimeZone('UTC')))->getTimestamp();
        $twoHoursAgo = (new \DateTimeImmutable('2021-05-15 14:17:00', new \DateTimeZone('UTC')))->getTimestamp();

        Assert::assertEquals($now, $warnResults['hits']['hits'][0]['_source']['timestamp']);
        Assert::assertEquals($oneHourAgo, $warnResults['hits']['hits'][1]['_source']['timestamp']);
        Assert::assertEquals($twoHoursAgo, $warnResults['hits']['hits'][2]['_source']['timestamp']);

        Assert::assertEquals($now, $errorResults['hits']['hits'][0]['_source']['timestamp']);
        Assert::assertEquals($oneHourAgo, $errorResults['hits']['hits'][1]['_source']['timestamp']);
        Assert::assertEquals($twoHoursAgo, $errorResults['hits']['hits'][2]['_source']['timestamp']);
    }

    public function test_it_purges_nothing_if_there_is_no_error_logs_to_purge()
    {
        $interval = new \DateInterval('PT1H');
        // We generate logs for each hour and we iterate 10 times
        $this->generateLogs(
            '2021-05-15 16:17:00',
            $interval,
            10,
            [EventsApiDebugLogLevels::INFO, EventsApiDebugLogLevels::NOTICE]
        );

        // We want to purge errors that are older than 4h
        $this->purgeErrorLogsQuery->execute(
            (new \DateTimeImmutable('2021-05-15 16:17:00', new \DateTimeZone('UTC')))
                ->sub(new \DateInterval('PT4H'))
        );
        $this->esClient->refreshIndex();
        $infoResults = $this->findDocumentsByLevel(EventsApiDebugLogLevels::INFO);
        $noticeResults = $this->findDocumentsByLevel(EventsApiDebugLogLevels::NOTICE);
        $errorResults = $this->findDocumentsByLevel(EventsApiDebugLogLevels::ERROR);
        $warnResults = $this->findDocumentsByLevel(EventsApiDebugLogLevels::WARNING);

        Assert::assertEquals(10, $infoResults['hits']['total']['value']);
        Assert::assertEquals(10, $noticeResults['hits']['total']['value']);
        Assert::assertEquals(0, $errorResults['hits']['total']['value']);
        Assert::assertEquals(0, $warnResults['hits']['total']['value']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeErrorLogsQuery = $this->get(PurgeEventsApiErrorLogsQuery::class);
        $this->esClient = $this->get('akeneo_connectivity.client.events_api_debug');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function buildDocument(\DateTime $fromDatetime, \DateInterval $interval, string $level, int $number): array
    {
        $content = [
            'message' => 'There is something to log, you may not have the permission to see the product or it does not exist.'
        ];
        $documents = [];
        for ($i = 0 ; $i < $number ; $i++) {
            $documents[] = [
                'content' => $content,
                'level' => $level,
                'timestamp' => $fromDatetime->getTimestamp(),
            ];
            $fromDatetime->sub($interval);
        }
        return $documents;
    }

    private function generateLogs(string $fromDatetime, \DateInterval $interval, int $number, array $levels): void
    {
        foreach ($levels as $level) {
            $documents = $this->buildDocument(
                new \DateTime($fromDatetime, new \DateTimeZone('UTC')),
                $interval,
                $level,
                $number
            );

            $this->esClient->bulkIndexes($documents);
        }
        $this->esClient->refreshIndex();
    }

    private function findDocumentsByLevel(string $level): array
    {
        return $this->esClient->search([
            '_source' => ['timestamp'],
            'sort' => [['timestamp' => ['order' => 'DESC']]],
            'size' => 20,
            'query' => [
                'bool' => [
                    'filter' => ['term' => ['level' => $level]]
                ]
            ]
        ]);
    }
}
