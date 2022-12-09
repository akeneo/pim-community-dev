<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\PhpMemoryLimit;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductProjection;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\ChunkProductUuids;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ProductIndexerSpec extends ObjectBehavior
{
    function let(
        Client $productAndProductModelIndexClient,
        GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection,
        ChunkProductUuids $chunkProductUuids,
        PhpMemoryLimit $phpMemoryLimit
    ) {
        $this->beConstructedWith(
            $productAndProductModelIndexClient,
            $getElasticsearchProductProjection,
            $chunkProductUuids,
            $phpMemoryLimit
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductIndexer::class);
    }

    function it_is_an_indexer()
    {
        $this->shouldImplement(ProductIndexerInterface::class);
    }

    function it_bulk_indexes_products_from_uuids(
        Client $productAndProductModelIndexClient,
        GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection,
        ChunkProductUuids $chunkProductUuids,
        PhpMemoryLimit $phpMemoryLimit
    ) {
        $uuids = [Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()];
        $phpMemoryLimit->asBytesFromPHPConfig()->willReturn(1200);
        $chunkProductUuids->byRawValuesSize($uuids, 20)->willReturn([
            [$uuids[0]],
            [$uuids[1]],
            [$uuids[2]],
        ]);

        $chunk1 = [$this->getElasticSearchProjection('identifier_1', $uuids[0])];
        $chunk2 = [$this->getElasticSearchProjection('identifier_1', $uuids[1])];
        $chunk3 = [$this->getElasticSearchProjection('identifier_1', $uuids[2])];

        $getElasticsearchProductProjection
            ->fromProductUuids([$uuids[0]])
            ->shouldBeCalled()
            ->willReturn($chunk1);
        $getElasticsearchProductProjection
            ->fromProductUuids([$uuids[1]])
            ->shouldBeCalled()
            ->willReturn($chunk2);
        $getElasticsearchProductProjection
            ->fromProductUuids([$uuids[2]])
            ->shouldBeCalled()
            ->willReturn($chunk3);

        $productAndProductModelIndexClient->bulkIndexes(
            $chunk1,
            'id',
            Refresh::disable()
        )->shouldBeCalled();

        $productAndProductModelIndexClient->bulkIndexes(
            $chunk2,
            'id',
            Refresh::disable()
        )->shouldBeCalled();

        $productAndProductModelIndexClient->bulkIndexes(
            $chunk3,
            'id',
            Refresh::disable()
        )->shouldBeCalled();

        $this->indexFromProductUuids($uuids);
    }

    function it_does_not_bulk_index_empty_arrays_of_identifiers(
        Client $productAndProductModelIndexClient,
        GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection
    ) {
        $getElasticsearchProductProjection->fromProductUuids(Argument::cetera())->shouldNotBeCalled();
        $productAndProductModelIndexClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->indexFromProductUuids([]);
    }

    function it_bulk_deletes_products_from_elasticsearch_index(Client $productAndProductModelIndexClient)
    {
        $productAndProductModelIndexClient->bulkDelete([
            'product_54162e35-ff81-48f1-96d5-5febd3f00fd5',
            'product_d9f573cc-8905-4949-8151-baf9d5328f26'
        ])->shouldBeCalled();

        $this->removeFromProductUuids([
            Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'),
            Uuid::fromString('d9f573cc-8905-4949-8151-baf9d5328f26')
        ])->shouldReturn(null);
    }

    function it_indexes_products_from_identifiers_and_waits_for_index_refresh(
        Client $productAndProductModelIndexClient,
        GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection,
        ChunkProductUuids $chunkProductUuids,
        PhpMemoryLimit $phpMemoryLimit
    ) {
        $uuids = [Uuid::uuid4(), Uuid::uuid4()];
        $phpMemoryLimit->asBytesFromPHPConfig()->willReturn(1200);
        $chunkProductUuids->byRawValuesSize($uuids, 20)->willReturn([$uuids]);

        $iterable = [
            $this->getElasticSearchProjection('identifier_1', $uuids[0]),
            $this->getElasticSearchProjection('identifier_2', $uuids[1])
        ];

        $getElasticsearchProductProjection
            ->fromProductUuids($uuids)
            ->shouldBeCalled()
            ->willReturn($iterable);

        $productAndProductModelIndexClient->bulkIndexes(
            $iterable,
            'id',
            Refresh::waitFor()
        )->shouldBeCalled();

        $this->indexFromProductUuids($uuids, ['index_refresh' => Refresh::waitFor()]);
    }

    function it_indexes_products_from_identifiers_and_disables_index_refresh_by_default(
        Client $productAndProductModelIndexClient,
        GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection,
        ChunkProductUuids $chunkProductUuids,
        PhpMemoryLimit $phpMemoryLimit
    ) {
        $uuids = [Uuid::uuid4(), Uuid::uuid4()];
        $phpMemoryLimit->asBytesFromPHPConfig()->willReturn(1200);
        $chunkProductUuids->byRawValuesSize($uuids, 20)->willReturn([$uuids]);

        $iterable = [
            $this->getElasticSearchProjection('identifier_1', $uuids[0]),
            $this->getElasticSearchProjection('identifier_2', $uuids[1])
        ];

        $getElasticsearchProductProjection
            ->fromProductUuids($uuids)
            ->shouldBeCalled()
            ->willReturn($iterable);

        $productAndProductModelIndexClient->bulkIndexes(
            $iterable,
            'id',
            Refresh::disable()
        )->shouldBeCalled();

        $this->indexFromProductUuids($uuids, ['index_refresh' => Refresh::disable()]);
    }

    function it_indexes_products_from_identifiers_and_enable_index_refresh_without_waiting_for_it(
        Client $productAndProductModelIndexClient,
        GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection,
        ChunkProductUuids $chunkProductUuids,
        PhpMemoryLimit $phpMemoryLimit
    ) {
        $uuids = [Uuid::uuid4(), Uuid::uuid4()];
        $phpMemoryLimit->asBytesFromPHPConfig()->willReturn(1200);
        $chunkProductUuids->byRawValuesSize($uuids, 20)->willReturn([$uuids]);
        $iterable = [
            $this->getElasticSearchProjection('identifier_1', $uuids[0]),
            $this->getElasticSearchProjection('identifier_2', $uuids[1])
        ];

        $getElasticsearchProductProjection
            ->fromProductUuids($uuids)
            ->shouldBeCalled()
            ->willReturn($iterable);

        $productAndProductModelIndexClient->bulkIndexes(
            $iterable,
            'id',
            Refresh::enable()
        )->shouldBeCalled();

        $this->indexFromProductUuids($uuids, ['index_refresh' => Refresh::enable()]);
    }

    private function getElasticSearchProjection(string $identifier, $uuid = null): ElasticsearchProductProjection
    {
        return new ElasticsearchProductProjection(
            $uuid ?? Uuid::fromString('3bf35583-c54e-4f8a-8bd9-5693c142a1cf'),
            $identifier,
            new \DateTimeImmutable('2019-03-16 12:03:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-03-16 12:03:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-03-16 12:03:00', new \DateTimeZone('UTC')),
            true,
            'family_code',
            [],
            'family_variant_code',
            [],
            [],
            [],
            [],
            null,
            [],
            [],
            [],
            [],
            [],
            [],
            []
        );
    }

    private function getRangeUuids(int $start, int $end): array
    {
        return array_map(fn (): UuidInterface => Uuid::uuid4(), range($start, $end));
    }
}
