<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Elasticsearch\Query;

use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query\PurgeEventsApiLogsQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;

class PurgeEventsApiLogsQueryIntegration extends TestCase
{
    /** @var Client */
    private $esClient;

    /** @var PurgeEventsApiLogsQuery */
    private $purgeQuery;

    public function test_it_purges_infos_over_the_given_number()
    {
        $interval = new \DateInterval('PT1H');
        // We generate an error each hour and we iterate 10 times
        $this->generateLogs($interval, 10);

        // We want to keep only 8 infos & notices
        $this->purgeQuery->execute(8, 10);
        $this->esClient->refreshIndex();
        $infoResults = $this->findDocumentsByLevel('info');
        $noticeResults = $this->findDocumentsByLevel('notice');
        $errorResults = $this->findDocumentsByLevel('error');
        $warnResults = $this->findDocumentsByLevel('warn');

        Assert::assertEquals(4, $infoResults['hits']['total']['value']);
        Assert::assertEquals(4, $noticeResults['hits']['total']['value']);
        Assert::assertEquals(10, $errorResults['hits']['total']['value']);
        Assert::assertEquals(10, $warnResults['hits']['total']['value']);
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeQuery = $this->get('akeneo_connectivity_connection.persistence.query.purge_events_api_info_notices_logs');
        $this->esClient = $this->get('akeneo_connectivity.client.events_api_debug');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function buildDocument(\DateInterval $interval, string $level, int $number): array
    {
        $content = [
            'code' => 422,
            '_links' => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_products'
                ]
            ],
            'message' => 'Property "description" does not exist. Check the expected format on the API documentation.'
        ];
        $documents = [];
        $datetime = new \DateTime('now');
        for ($i = 0 ; $i < $number ; $i++) {
            $documents[] = [
                'content' => $content,
                'level' => $level,
                'timestamp' => $datetime->timestamp,
            ];
            $datetime->sub($interval);
        }
        return $documents;
    }

    private function generateLogs(\DateInterval $interval, int $number): void
    {
        foreach (array( 'info', 'notice', 'warn', 'error') as $level) {
            $documents = $this->buildDocument($interval, $level, $number);

            $this->esClient->bulkIndexes($documents);
            $this->esClient->refreshIndex();
        }
    }
}
