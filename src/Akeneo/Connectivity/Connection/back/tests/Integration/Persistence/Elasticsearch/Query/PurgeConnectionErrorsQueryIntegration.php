<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Elasticsearch\Query;

use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query\PurgeConnectionErrorsQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;

class PurgeConnectionErrorsQueryIntegration extends TestCase
{
    private Client $esClient;
    private PurgeConnectionErrorsQuery $purgeQuery;

    public function test_it_purges_errors_older_than_the_given_days()
    {
        $interval = new \DateInterval('PT6H');

        // We generate an error each 6 hour and we iterate 20 times
        $this->generateErrors(['magento', 'erp', 'amazon'], $interval, 20);

        // We want to keep two days of errors for magento and erp connections
        $this->purgeQuery->execute(['magento', 'erp'], 100, 2);
        $this->esClient->refreshIndex();
        $amazonResults = $this->findDocumentsByConnectionCode('amazon');
        $magentoResults = $this->findDocumentsByConnectionCode('magento');
        $erpResults = $this->findDocumentsByConnectionCode('erp');

        Assert::assertEquals(0, $amazonResults['hits']['total']['value']);
        // 8 corresponds to 4 errors by days during 2 days
        // 6 hours of interval during generation of errors and 2 days to keep during the purge
        Assert::assertEquals(8, $magentoResults['hits']['total']['value']);
        Assert::assertEquals(8, $erpResults['hits']['total']['value']);
    }

    public function test_it_purges_errors_over_the_given_number()
    {
        $interval = new \DateInterval('PT1H');
        // We generate an error each hour and we iterate 10 times
        $this->generateErrors(['magento', 'erp', 'amazon'], $interval, 10);

        // We want to keep only 5 errors by connection
        $this->purgeQuery->execute(['magento', 'erp'], 5, 10);
        $this->esClient->refreshIndex();
        $amazonResults = $this->findDocumentsByConnectionCode('amazon');
        $magentoResults = $this->findDocumentsByConnectionCode('magento');
        $erpResults = $this->findDocumentsByConnectionCode('erp');

        Assert::assertEquals(0, $amazonResults['hits']['total']['value']);
        Assert::assertEquals(5, $magentoResults['hits']['total']['value']);
        Assert::assertEquals(5, $erpResults['hits']['total']['value']);
    }

    private function findDocumentsByConnectionCode(string $code): array
    {
        return $this->esClient->search([
            '_source' => ['connection_code', 'error_datetime'],
            'sort' => [['error_datetime' => ['order' => 'DESC']]],
            'size' => 20,
            'query' => [
                'bool' => [
                    'filter' => ['term' => ['connection_code' => $code]]
                ]
            ]
        ]);
    }
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeQuery = $this->get('akeneo_connectivity_connection.persistence.query.purge_connection_errors');
        $this->esClient = $this->get('akeneo_connectivity.client.connection_error');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function generateErrors(array $connectionCodes, \DateInterval $interval, int $number): void
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
        foreach ($connectionCodes as $connectionCode) {
            $datetime = new \DateTime('now');
            for ($i = 0 ; $i < $number ; $i++) {
                $documents[] = [
                    'connection_code' => $connectionCode,
                    'content' => $content,
                    'error_datetime' => $datetime->format(\DateTimeInterface::ATOM),
                ];
                $datetime->sub($interval);
            }
        }

        $this->esClient->bulkIndexes($documents);
        $this->esClient->refreshIndex();
    }
}
