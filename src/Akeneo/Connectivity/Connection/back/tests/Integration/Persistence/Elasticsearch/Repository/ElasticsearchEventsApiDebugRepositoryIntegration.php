<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Elasticsearch\Repository;

use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Repository\ElasticsearchEventsApiDebugRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ElasticsearchEventsApiDebugRepositoryIntegration extends TestCase
{
    private ElasticsearchEventsApiDebugRepository $elasticsearchEventsApiDebugRepository;
    private Client $elasticsearchClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->elasticsearchEventsApiDebugRepository = $this->get('akeneo_connectivity.connection.persistence.repository.events_api_debug');
        $this->elasticsearchClient = $this->get('akeneo_connectivity.client.events_api_debug');
    }

    public function test_it_bulk_inserts(): void
    {
        $documents = [
            [
                'timestamp' => 631152000,
                'level' => 'info',
                'message' => 'An information message.',
                'connection_code' => null,
                'context' => [
                    'data' => 'Some more informations.',
                ],
            ],
            [
                'timestamp' => 946684800,
                'level' => 'warning',
                'message' => 'A warning message!',
                'connection_code' => 'erp_0000',
                'context' => [],
            ],
        ];

        $this->elasticsearchEventsApiDebugRepository->bulkInsert($documents);

        $this->elasticsearchClient->refreshIndex();
        $result = $this->elasticsearchClient->search([]);

        Assert::assertCount(2, $result['hits']['hits']);

        Assert::assertEquals(
            [
                'timestamp' => 631152000,
                'level' => 'info',
                'message' => 'An information message.',
                'connection_code' => null,
                'context' => [
                    'data' => 'Some more informations.',
                ],
            ],
            $result['hits']['hits'][0]['_source']
        );

        Assert::assertEquals(
            [
                'timestamp' => 946684800,
                'level' => 'warning',
                'message' => 'A warning message!',
                'connection_code' => 'erp_0000',
                'context' => [],
            ],
            $result['hits']['hits'][1]['_source']
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
