<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductProjection;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductIndexerSpec extends ObjectBehavior
{
    function let(
        Client $productAndProductModelIndexClient,
        GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection
    ) {
        $this->beConstructedWith($productAndProductModelIndexClient, $getElasticsearchProductProjection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductIndexer::class);
    }

    function it_is_an_indexer()
    {
        $this->shouldImplement(ProductIndexerInterface::class);
    }

    function it_indexes_a_single_product_from_identifier(
        Client $productAndProductModelIndexClient,
        GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection
    ) {
        $identifier = 'foobar';
        $iterable = [$this->getElasticSearchProjection('identifier_1')];
        $getElasticsearchProductProjection->fromProductIdentifiers([$identifier])->willReturn($iterable);
        $productAndProductModelIndexClient
            ->bulkIndexes(
                $iterable,
                'id',
                Refresh::disable()
            )->shouldBeCalled();

        $this->indexFromProductIdentifier($identifier);
    }

    function it_bulk_indexes_products_from_identifiers(
        Client $productAndProductModelIndexClient,
        GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection
    ) {
        $identifiers = ['foo', 'bar', 'unknown'];

        $iterable = [$this->getElasticSearchProjection('identifier_1'), $this->getElasticSearchProjection('identifier_2')];
        $getElasticsearchProductProjection->fromProductIdentifiers($identifiers)->willReturn($iterable);

        $productAndProductModelIndexClient->bulkIndexes(
            $iterable,
            'id',
            Refresh::disable()
        )->shouldBeCalled();

        $this->indexFromProductIdentifiers($identifiers);
    }

    function it_bulk_indexes_products_from_identifiers_using_batch(
        Client $productAndProductModelIndexClient,
        GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection
    ) {
        $identifiers = $this->getRangeIdentifiers(1, 1502);

        $getElasticsearchProductProjection->fromProductIdentifiers($this->getRangeIdentifiers(1, 500))
            ->willReturn([$this->getElasticSearchProjection('identifier_1')]);
        $getElasticsearchProductProjection->fromProductIdentifiers($this->getRangeIdentifiers(501, 1000))
            ->willReturn([$this->getElasticSearchProjection('identifier_2')]);
        $getElasticsearchProductProjection->fromProductIdentifiers($this->getRangeIdentifiers(1001, 1500))
            ->willReturn([$this->getElasticSearchProjection('identifier_3')]);
        $getElasticsearchProductProjection->fromProductIdentifiers($this->getRangeIdentifiers(1501, 1502))
            ->willReturn([$this->getElasticSearchProjection('identifier_4')]);

        $productAndProductModelIndexClient->bulkIndexes(
            Argument::any(),
            'id',
            Refresh::disable()
        )->shouldBeCalledTimes(4);

        $this->indexFromProductIdentifiers($identifiers);
    }

    function it_does_not_bulk_index_empty_arrays_of_identifiers(
        Client $productAndProductModelIndexClient,
        GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection
    ) {
        $getElasticsearchProductProjection->fromProductIdentifiers(Argument::cetera())->shouldNotBeCalled();
        $productAndProductModelIndexClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->indexFromProductIdentifiers([]);
    }

    function it_deletes_products_from_elasticsearch_index(Client $productAndProductModelIndexClient)
    {
        $productAndProductModelIndexClient->delete('product_40')->shouldBeCalled();

        $this->removeFromProductId(40)->shouldReturn(null);
    }

    function it_bulk_deletes_products_from_elasticsearch_index(Client $productAndProductModelIndexClient)
    {
        $productAndProductModelIndexClient->bulkDelete(['product_40', 'product_33'])
            ->shouldBeCalled();

        $this->removeFromProductIds([40, 33])->shouldReturn(null);
    }

    function it_indexes_products_from_identifiers_and_waits_for_index_refresh(
        Client $productAndProductModelIndexClient,
        GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection
    ) {
        $iterable = [$this->getElasticSearchProjection('identifier_1')];
        $getElasticsearchProductProjection
            ->fromProductIdentifiers(['identifier_1'])
            ->willReturn($iterable);

        $productAndProductModelIndexClient->bulkIndexes(
            $iterable,
            'id',
            Refresh::waitFor()
        )->shouldBeCalled();

        $this->indexFromProductIdentifiers(['identifier_1'], ['index_refresh' => Refresh::waitFor()]);
    }

    function it_indexes_products_from_identifiers_and_disables_index_refresh_by_default(
        Client $productAndProductModelIndexClient,
        GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection
    ) {
        $identifiers = ['foo', 'bar', 'unknown'];

        $iterable = [$this->getElasticSearchProjection('identifier_1'), $this->getElasticSearchProjection('identifier_2')];
        $getElasticsearchProductProjection->fromProductIdentifiers($identifiers)
            ->willReturn($iterable);

        $productAndProductModelIndexClient->bulkIndexes(
            $iterable,
            'id',
            Refresh::disable()
        )->shouldBeCalled();

        $this->indexFromProductIdentifiers($identifiers, ['index_refresh' => Refresh::disable()]);
    }

    function it_indexes_products_from_identifiers_and_enable_index_refresh_without_waiting_for_it(
        Client $productAndProductModelIndexClient,
        GetElasticsearchProductProjectionInterface $getElasticsearchProductProjection
    ) {
        $identifiers = ['foo', 'bar', 'unknown'];

        $iterable = [$this->getElasticSearchProjection('identifier_1'), $this->getElasticSearchProjection('identifier_2')];
        $getElasticsearchProductProjection->fromProductIdentifiers($identifiers)
            ->willReturn($iterable);

        $productAndProductModelIndexClient->bulkIndexes(
            $iterable,
            'id',
            Refresh::enable()
        )->shouldBeCalled();

        $this->indexFromProductIdentifiers($identifiers, ['index_refresh' => Refresh::enable()]);
    }

    private function getElasticSearchProjection(string $identifier): ElasticsearchProductProjection
    {
        return new ElasticsearchProductProjection(
            '1',
            '1e40-4c55-a415-89c7958b270d',
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

    private function getRangeIdentifiers(int $start, int $end): array
    {
        return preg_filter('/^/', 'p_', range($start, $end));
    }
}
