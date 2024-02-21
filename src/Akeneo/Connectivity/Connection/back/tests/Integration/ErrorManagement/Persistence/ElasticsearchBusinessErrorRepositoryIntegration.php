<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\ErrorManagement\Persistence;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence\ElasticsearchBusinessErrorRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client as ElasticsearchClient;
use PHPUnit\Framework\Assert;

class ElasticsearchBusinessErrorRepositoryIntegration extends TestCase
{
    public function test_it_bulk_inserts(): void
    {
        /** @var ElasticsearchBusinessErrorRepository */
        $repository = $this->get(ElasticsearchBusinessErrorRepository::class);

        $repository->bulkInsert(
            new ConnectionCode('erp'),
            [
                new BusinessError(
                    '{"message":"First error!"}',
                    new \DateTimeImmutable('2019-12-31T00:00:00+00:00')
                ),
                new BusinessError(
                    '{"message":"Second error!","property":"name"}',
                    new \DateTimeImmutable('2020-01-01T00:00:00+00:00')
                ),
            ]
        );

        /** @var ElasticsearchClient */
        $client = $this->get('akeneo_connectivity.client.connection_error');

        $client->refreshIndex();
        $result = $client->search([]);

        $doc1 = $result['hits']['hits'][0]['_source'];
        $doc2 = $result['hits']['hits'][1]['_source'];

        Assert::assertCount(2, $result['hits']['hits']);
        Assert::assertArrayHasKey('id', $doc1);
        Assert::assertEquals('erp', $doc1['connection_code']);
        Assert::assertEquals(['message' => 'First error!'], $doc1['content']);
        Assert::assertEquals('2019-12-31T00:00:00+00:00', $doc1['error_datetime']);

        Assert::assertArrayHasKey('id', $doc2);
        Assert::assertEquals('erp', $doc2['connection_code']);
        Assert::assertEquals(['message' => 'Second error!', 'property' => 'name'], $doc2['content']);
        Assert::assertEquals('2020-01-01T00:00:00+00:00', $doc2['error_datetime']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
