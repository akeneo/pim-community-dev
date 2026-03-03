<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook\EventsApiDebug\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Persistence\ElasticsearchEventsApiDebugRepository;
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

        $this->elasticsearchEventsApiDebugRepository = $this->get(ElasticsearchEventsApiDebugRepository::class);
        $this->elasticsearchClient = $this->get('akeneo_connectivity.client.events_api_debug');
    }

    public function test_it_saves_logs(): void
    {
        $this->elasticsearchEventsApiDebugRepository->persist([
            'id' => 'bb1ff8f4-bad8-4c5e-8d42-6116c23a3629',
            'timestamp' => 631152000,
            'level' => 'info',
            'message' => 'An information message.',
            'connection_code' => null,
            'context' => [
                'data' => 'Some more informations.',
                'other_data' => 'Other important data',
                'more' => [
                    'more_other_data' => 'Deep data'
                ],
            ],
        ]);
        $this->elasticsearchEventsApiDebugRepository->persist([
            'id' => '02cac8dc-5b75-11ed-9b6a-0242ac120002',
            'timestamp' => 946684800,
            'level' => 'warning',
            'message' => 'A warning message!',
            'connection_code' => 'erp_0000',
            'context' => [],
        ]);
        $this->elasticsearchEventsApiDebugRepository->flush();

        $this->elasticsearchClient->refreshIndex();
        $result = $this->elasticsearchClient->search([]);

        Assert::assertCount(2, $result['hits']['hits']);

        Assert::assertEquals(
            [
                'id' => 'bb1ff8f4-bad8-4c5e-8d42-6116c23a3629',
                'timestamp' => 631152000,
                'level' => 'info',
                'message' => 'An information message.',
                'connection_code' => null,
                'context' => [
                    'data' => 'Some more informations.',
                    'other_data' => 'Other important data',
                    'more' => [
                        'more_other_data' => 'Deep data'
                    ],
                ],
                'context_flattened' => 'Some more informations. Other important data Deep data',
            ],
            $result['hits']['hits'][0]['_source']
        );

        Assert::assertEquals(
            [
                'id' => '02cac8dc-5b75-11ed-9b6a-0242ac120002',
                'timestamp' => 946684800,
                'level' => 'warning',
                'message' => 'A warning message!',
                'connection_code' => 'erp_0000',
                'context' => [],
                'context_flattened' => '',
            ],
            $result['hits']['hits'][1]['_source']
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
