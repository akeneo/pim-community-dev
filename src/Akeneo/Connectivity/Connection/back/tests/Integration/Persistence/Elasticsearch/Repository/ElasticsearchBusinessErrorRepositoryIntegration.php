<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Elasticsearch\Repository;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Repository\ElasticsearchBusinessErrorRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client as ElasticsearchClient;
use PHPUnit\Framework\Assert;

class ElasticsearchBusinessErrorRepositoryIntegration extends TestCase
{
    public function test_it_bulk_inserts(): void
    {
        /** @var ElasticsearchBusinessErrorRepository */
        $repository = $this->get('akeneo_connectivity.connection.persistence.repository.business_error');

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

        Assert::assertEquals([
            'connection_code' => 'erp',
            'content' => ['message' => 'First error!'],
            'error_datetime' => '2019-12-31T00:00:00+00:00',
        ], $doc1);

        Assert::assertEquals([
            'connection_code' => 'erp',
            'content' => ['message' => 'Second error!', 'property' => 'name'],
            'error_datetime' => '2020-01-01T00:00:00+00:00'
        ], $doc2);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
