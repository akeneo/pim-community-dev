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

        $repository->bulkInsert([
            new BusinessError(new ConnectionCode('erp'), '{"message":"First error!"}'),
            new BusinessError(new ConnectionCode('erp'), '{"message":"Second error!","property":"name"}'),
        ]);

        /** @var ElasticsearchClient */
        $client = $this->get('akeneo_connectivity.client.connection_error');

        $client->refreshIndex();
        $result = $client->search([]);

        $doc1 = $result['hits']['hits'][0]['_source'];
        $doc2 = $result['hits']['hits'][1]['_source'];

        Assert::assertCount(2, $result['hits']['hits']);

        Assert::assertArrayHasKey('error_datetime', $doc1);
        Assert::assertSame('erp', $doc1['connection_code']);
        Assert::assertSame(['message' => 'First error!'], $doc1['content']);

        Assert::assertArrayHasKey('error_datetime', $doc2);
        Assert::assertSame('erp', $doc2['connection_code']);
        Assert::assertSame(['message' => 'Second error!', 'property' => 'name'], $doc2['content']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
