<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Elasticsearch\Query;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\EventsApiDebugLogLevels;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query\PurgeEventsApiSuccessLogsQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;

class PurgeEventsApiSuccessLogsQueryIntegration extends TestCase
{
    private Client $esClient;
    private PurgeEventsApiSuccessLogsQuery $purgeQuery;

    public function test_it_purges_infos_over_the_given_number()
    {
        $interval = new \DateInterval('PT1H');
        // We generate logs for each hour and we iterate 10 times
        $this->generateLogs(
            $interval,
            10,
            [
                EventsApiDebugLogLevels::ERROR,
                EventsApiDebugLogLevels::WARNING,
                EventsApiDebugLogLevels::NOTICE,
                EventsApiDebugLogLevels::INFO,
            ]
        );

        // We want to keep only 8 infos & notices
        $this->purgeQuery->execute(8);
        $this->esClient->refreshIndex();
        $infoResults = $this->findDocumentsByLevel(EventsApiDebugLogLevels::INFO);
        $noticeResults = $this->findDocumentsByLevel(EventsApiDebugLogLevels::NOTICE);
        $errorResults = $this->findDocumentsByLevel(EventsApiDebugLogLevels::ERROR);
        $warnResults = $this->findDocumentsByLevel(EventsApiDebugLogLevels::WARNING);

        Assert::assertEquals(4, $infoResults['hits']['total']['value']);
        Assert::assertEquals(4, $noticeResults['hits']['total']['value']);
        Assert::assertEquals(10, $errorResults['hits']['total']['value']);
        Assert::assertEquals(10, $warnResults['hits']['total']['value']);
    }

    public function test_it_purges_nothing_if_no_notice_or_info()
    {
        $interval = new \DateInterval('PT1H');
        // We generate logs for each hour and we iterate 10 times
        $this->generateLogs(
            $interval,
            10,
            [EventsApiDebugLogLevels::WARNING, EventsApiDebugLogLevels::ERROR]
        );

        // We want to keep only 8 infos & notices
        $this->purgeQuery->execute(8);
        $this->esClient->refreshIndex();
        $infoResults = $this->findDocumentsByLevel(EventsApiDebugLogLevels::INFO);
        $noticeResults = $this->findDocumentsByLevel(EventsApiDebugLogLevels::NOTICE);
        $errorResults = $this->findDocumentsByLevel(EventsApiDebugLogLevels::ERROR);
        $warnResults = $this->findDocumentsByLevel(EventsApiDebugLogLevels::WARNING);

        Assert::assertEquals(0, $infoResults['hits']['total']['value']);
        Assert::assertEquals(0, $noticeResults['hits']['total']['value']);
        Assert::assertEquals(10, $errorResults['hits']['total']['value']);
        Assert::assertEquals(10, $warnResults['hits']['total']['value']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeQuery = $this->get('akeneo_connectivity_connection.persistence.query.purge_events_api_success_logs');
        $this->esClient = $this->get('akeneo_connectivity.client.events_api_debug');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function buildDocument(\DateInterval $interval, string $level, int $number): array
    {
        $content = [
            'message' => 'There is something to log, you may not have the permission to see the product or it does not exist.'
        ];
        $documents = [];
        $datetime = new \DateTime('now');
        for ($i = 0 ; $i < $number ; $i++) {
            $documents[] = [
                'content' => $content,
                'level' => $level,
                'timestamp' => $datetime->getTimestamp(),
            ];
            $datetime->sub($interval);
        }
        return $documents;
    }

    private function generateLogs(\DateInterval $interval, int $number, array $levels): void
    {
        foreach ($levels as $level) {
            $documents = $this->buildDocument($interval, $level, $number);

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
