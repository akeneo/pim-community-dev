<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductModelProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductModelProjection;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductModelIndexerSpec extends ObjectBehavior
{
    function let(
        Client $productAndProductModelClient,
        GetElasticsearchProductModelProjectionInterface $getElasticsearchProductModelProjection
    ) {
        $this->beConstructedWith($productAndProductModelClient, $getElasticsearchProductModelProjection);
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
        GetElasticsearchProductModelProjectionInterface $getElasticsearchProductModelProjection
    ) {
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
        GetElasticsearchProductModelProjectionInterface $getElasticsearchProductModelProjection
    ) {
        $code1 = 'foo';
        $code2 = 'bar';
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

        $this->indexFromProductModelCodes([$code1, $code2]);
    }

    function it_bulk_indexes_products_from_identifiers_using_batch(
        Client $productAndProductModelClient,
        GetElasticsearchProductModelProjectionInterface $getElasticsearchProductModelProjection
    ) {
        $identifiers = $this->getRangeCodes(1, 1502);

        $getElasticsearchProductModelProjection->fromProductModelCodes($this->getRangeCodes(1, 500))
            ->willYield([$this->getFakeProjection('identifier_1')]);
        $getElasticsearchProductModelProjection->fromProductModelCodes($this->getRangeCodes(501, 1000))
            ->willYield([$this->getFakeProjection('identifier_2')]);
        $getElasticsearchProductModelProjection->fromProductModelCodes($this->getRangeCodes(1001, 1500))
            ->willYield([$this->getFakeProjection('identifier_3')]);
        $getElasticsearchProductModelProjection->fromProductModelCodes($this->getRangeCodes(1501, 1502))
            ->willYield([$this->getFakeProjection('identifier_4')]);

        $productAndProductModelClient->bulkIndexes(
            Argument::type(\Traversable::class),
            'id',
            Refresh::disable()
        )->shouldBeCalledTimes(4);

        $this->indexFromProductModelCodes($identifiers);
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
        GetElasticsearchProductModelProjectionInterface $getElasticsearchProductModelProjection
    ) {
        $code1 = 'foo';
        $code2 = 'bar';
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
        GetElasticsearchProductModelProjectionInterface $getElasticsearchProductModelProjection
    ) {
        $code1 = 'foo';
        $code2 = 'bar';
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
