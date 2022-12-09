<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductModelProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\PhpMemoryLimit;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductModelProjection;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\ChunkProductUuids;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\ChunkProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class ProductModelIndexerSpec extends ObjectBehavior
{
    function let(
        Client $productAndProductModelClient,
        GetElasticsearchProductModelProjectionInterface $getElasticsearchProductModelProjection,
        ChunkProductModelCodes $chunkProductModelCodes,
        PhpMemoryLimit $phpMemoryLimit
    ) {
        $this->beConstructedWith(
            $productAndProductModelClient,
            $getElasticsearchProductModelProjection,
            $chunkProductModelCodes,
            $phpMemoryLimit
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelIndexer::class);
    }

    function it_is_a_product_model_indexer()
    {
        $this->shouldImplement(ProductModelIndexerInterface::class);
    }

    function it_indexes_a_single_product_model(
        Client $productAndProductModelClient,
        GetElasticsearchProductModelProjectionInterface $getElasticsearchProductModelProjection,
        ChunkProductModelCodes $chunkProductModelCodes,
        PhpMemoryLimit $phpMemoryLimit
    ) {
        $phpMemoryLimit->asBytesFromPHPConfig()->willReturn(1200);
        $chunkProductModelCodes->byRawValuesSize(['foobar'], 20)->willReturn([
            ['foobar'],
        ]);
        $code = 'foobar';
        $getElasticsearchProductModelProjection->fromProductModelCodes([$code])->willYield([
            $code => $this->getFakeProjection()
        ]);
        $productAndProductModelClient
            ->bulkIndexes(
                Argument::that(function ($projections) use ($code) {
                    return \iterator_to_array($projections) === [$code => $this->getFakeProjection()->toArray()];
                }),
                'id',
                Refresh::disable()
            )->shouldBeCalled();

        $this->indexFromProductModelCode($code);
    }

    function it_bulk_indexes_product_models(
        Client $productAndProductModelClient,
        GetElasticsearchProductModelProjectionInterface $getElasticsearchProductModelProjection,
        ChunkProductModelCodes $chunkProductModelCodes,
        PhpMemoryLimit $phpMemoryLimit
    ) {
        $phpMemoryLimit->asBytesFromPHPConfig()->willReturn(1200);
        $chunkProductModelCodes->byRawValuesSize(['foo', 'bar'], 20)->willReturn([['foo'], ['bar']]);

        $code1 = 'foo';
        $code2 = 'bar';
        $getElasticsearchProductModelProjection->fromProductModelCodes([$code1])->willYield([
            $code1 => $this->getFakeProjection($code1),
        ]);

        $getElasticsearchProductModelProjection->fromProductModelCodes([$code2])->willYield([
            $code2 => $this->getFakeProjection($code2),
        ]);

        $productAndProductModelClient->bulkIndexes(
            Argument::type(\Traversable::class),
            'id',
            Refresh::disable()
        )->shouldBeCalledTimes(2);


        $this->indexFromProductModelCodes([$code1, $code2]);
    }

    function it_deletes_product_models_from_elasticsearch_index($productAndProductModelClient)
    {
        $productAndProductModelClient->deleteByQuery([
            'query' => [
                'bool' => [
                    'should' => [
                        ['terms' => ['id' => ['product_model_40']]],
                        ['terms' => ['ancestors.ids' => ['product_model_40']]],
                    ],
                ],
            ],
        ])->shouldBeCalled();

        $this->removeFromProductModelId(40)->shouldReturn(null);
    }

    function it_bulk_deletes_product_models_from_elasticsearch_index($productAndProductModelClient)
    {
        $productAndProductModelClient->deleteByQuery([
            'query' => [
                'bool' => [
                    'should' => [
                        ['terms' => ['id' => ['product_model_40', 'product_model_33']]],
                        ['terms' => ['ancestors.ids' => ['product_model_40', 'product_model_33']]],
                    ],
                ],
            ],
        ])->shouldBeCalled();

        $this->removeFromProductModelIds([40, 33])->shouldReturn(null);
    }

    function it_indexes_product_models_and_disable_index_refresh(
        Client $productAndProductModelClient,
        GetElasticsearchProductModelProjectionInterface $getElasticsearchProductModelProjection,
        ChunkProductModelCodes $chunkProductModelCodes,
        PhpMemoryLimit $phpMemoryLimit
    ) {
        $code1 = 'foo';
        $code2 = 'bar';
        $phpMemoryLimit->asBytesFromPHPConfig()->willReturn(1200);
        $chunkProductModelCodes->byRawValuesSize([$code1, $code2], 20)->willReturn([[$code1, $code2]]);

        $getElasticsearchProductModelProjection->fromProductModelCodes([$code1, $code2])->willYield([
            $code1 => $this->getFakeProjection($code1),
            $code2 => $this->getFakeProjection($code2)
        ]);
        $productAndProductModelClient->bulkIndexes(
            Argument::that(
                function ($projections) use ($code1, $code2) {
                    return \iterator_to_array($projections) === [
                            $code1 => $this->getFakeProjection($code1)->toArray(),
                            $code2 => $this->getFakeProjection($code2)->toArray(),
                        ];
                }
            ),
            'id',
            Refresh::disable()
        )->shouldBeCalled();

        $this->indexFromProductModelCodes([$code1, $code2], ['index_refresh' => Refresh::disable()]);
    }

    function it_indexes_product_models_and_enable_index_refresh_without_waiting_for_it(
        Client $productAndProductModelClient,
        GetElasticsearchProductModelProjectionInterface $getElasticsearchProductModelProjection,
        ChunkProductModelCodes $chunkProductModelCodes,
        PhpMemoryLimit $phpMemoryLimit
    ) {
        $code1 = 'foo';
        $code2 = 'bar';
        $phpMemoryLimit->asBytesFromPHPConfig()->willReturn(1200);
        $chunkProductModelCodes->byRawValuesSize([$code1, $code2], 20)->willReturn([[$code1, $code2]]);

        $getElasticsearchProductModelProjection->fromProductModelCodes([$code1, $code2])->willYield([
            $code1 => $this->getFakeProjection($code1),
            $code2 => $this->getFakeProjection($code2)
        ]);


        $productAndProductModelClient->bulkIndexes(
            Argument::that(
                function ($projections) use ($code1, $code2) {
                    return \iterator_to_array($projections) === [
                            $code1 => $this->getFakeProjection($code1)->toArray(),
                            $code2 => $this->getFakeProjection($code2)->toArray(),
                        ];
                }
            ),
            'id',
            Refresh::waitFor()
        )->shouldBeCalled();

        $this->indexFromProductModelCodes([$code1, $code2], ['index_refresh' => Refresh::waitFor()]);
    }

    private function getFakeProjection(string $code = 'code'): ElasticsearchProductModelProjection
    {
        return new ElasticsearchProductModelProjection(
            1,
            $code,
            new \DateTimeImmutable('2000-12-30'),
            new \DateTimeImmutable('2000-12-31'),
            new \DateTimeImmutable('2000-12-31'),
            'familyCode',
            [],
            'familyVariantCode',
            [],
            [],
            'parentCode',
            [],
            [],
            [],
            null,
            [],
            [],
            []
        );
    }

    private function getRangeCodes(int $start, int $end): array
    {
        return preg_filter('/^/', 'pm_', range($start, $end));
    }
}
