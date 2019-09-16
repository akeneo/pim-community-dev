<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\IndexableProduct;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductProjectionInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer;
use Prophecy\Argument;

class ProductIndexerSpec extends ObjectBehavior
{
    function let(
        Client $productAndProductModelIndexClient,
        GetElasticsearchProductProjectionInterface $getIndexableProduct
    ) {
        $this->beConstructedWith($productAndProductModelIndexClient, $getIndexableProduct);
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
        $productAndProductModelIndexClient,
        $getIndexableProduct,
        IndexableProduct $indexableProduct
    ) {
        $identifier = 'foobar';
        $getIndexableProduct->fromProductIdentifiers([$identifier])->willReturn([$indexableProduct]);
        $indexableProduct->toArray()->willReturn(['id' => $identifier, 'a key' => 'a value']);
        $productAndProductModelIndexClient
            ->bulkIndexes([['id' => $identifier, 'a key' => 'a value']], 'id', Refresh::disable())
            ->shouldBeCalled();

        $this->indexFromProductIdentifier($identifier);
    }

    function it_does_not_index_anything_if_identifier_is_unknown(
        $productAndProductModelIndexClient,
        $getIndexableProduct
    ) {
        $identifier = 'foobar';
        $getIndexableProduct->fromProductIdentifiers([$identifier])->willReturn([]);
        $productAndProductModelIndexClient->bulkIndexes(Argument::cetera())
            ->shouldNotBeCalled();

        $this->indexFromProductIdentifier($identifier);
    }

    function it_bulk_indexes_products_from_identifiers(
        $productAndProductModelIndexClient,
        $getIndexableProduct,
        IndexableProduct $indexableProduct1,
        IndexableProduct $indexableProduct2
    ) {
        $identifiers = ['foo', 'bar', 'unknown'];

        $getIndexableProduct->fromProductIdentifiers($identifiers)
            ->willReturn([$indexableProduct1, $indexableProduct2]);

        $indexableProduct1->toArray()->willReturn(['id' => $identifiers[0], 'a key' => 'a value']);
        $indexableProduct2->toArray()->willReturn(['id' => $identifiers[1], 'a key' => 'another value']);

        $productAndProductModelIndexClient->bulkIndexes([
            ['id' => $identifiers[0], 'a key' => 'a value'],
            ['id' => $identifiers[1], 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $this->indexFromProductIdentifiers($identifiers);
    }

    function it_does_not_bulk_index_empty_arrays_of_identifiers(
        $productAndProductModelIndexClient,
        $getIndexableProduct
    ) {
        $getIndexableProduct->fromProductIdentifiers(Argument::cetera())->shouldNotBeCalled();
        $productAndProductModelIndexClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->indexFromProductIdentifiers([]);
    }

    function it_deletes_products_from_elasticsearch_index($productAndProductModelIndexClient)
    {
        $productAndProductModelIndexClient->delete('product_40')->shouldBeCalled();

        $this->removeFromProductId(40)->shouldReturn(null);
    }

    function it_bulk_deletes_products_from_elasticsearch_index($productAndProductModelIndexClient)
    {
        $productAndProductModelIndexClient->bulkDelete(['product_40', 'product_33'])
            ->shouldBeCalled();

        $this->removeFromProductIds([40, 33])->shouldReturn(null);
    }

    function it_indexes_products_from_identifiers_and_waits_for_index_refresh(
        $productAndProductModelIndexClient,
        $getIndexableProduct,
        IndexableProduct $indexableProduct1,
        IndexableProduct $indexableProduct2
    ) {
        $identifiers = ['foo', 'bar', 'unknown'];

        $getIndexableProduct->fromProductIdentifiers($identifiers)
            ->willReturn([$indexableProduct1, $indexableProduct2]);

        $indexableProduct1->toArray()->willReturn(['id' => $identifiers[0], 'a key' => 'a value']);
        $indexableProduct2->toArray()->willReturn(['id' => $identifiers[1], 'a key' => 'another value']);

        $productAndProductModelIndexClient->bulkIndexes([
            ['id' => $identifiers[0], 'a key' => 'a value'],
            ['id' => $identifiers[1], 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $this->indexFromProductIdentifiers($identifiers, ['index_refresh' => Refresh::waitFor()]);
    }

    function it_indexes_products_from_identifiers_and_disables_index_refresh_by_default(
        $productAndProductModelIndexClient,
        $getIndexableProduct,
        IndexableProduct $indexableProduct1,
        IndexableProduct $indexableProduct2
    ) {
        $identifiers = ['foo', 'bar', 'unknown'];

        $getIndexableProduct->fromProductIdentifiers($identifiers)
            ->willReturn([$indexableProduct1, $indexableProduct2]);

        $indexableProduct1->toArray()->willReturn(['id' => $identifiers[0], 'a key' => 'a value']);
        $indexableProduct2->toArray()->willReturn(['id' => $identifiers[1], 'a key' => 'another value']);

        $productAndProductModelIndexClient->bulkIndexes([
            ['id' => $identifiers[0], 'a key' => 'a value'],
            ['id' => $identifiers[1], 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $this->indexFromProductIdentifiers($identifiers, ['index_refresh' => Refresh::disable()]);
    }

    function it_indexes_products_from_identifiers_and_enable_index_refresh_without_waiting_for_it(
        $productAndProductModelIndexClient,
        $getIndexableProduct,
        IndexableProduct $indexableProduct1,
        IndexableProduct $indexableProduct2
    ) {
        $identifiers = ['foo', 'bar', 'unknown'];

        $getIndexableProduct->fromProductIdentifiers($identifiers)
            ->willReturn([$indexableProduct1, $indexableProduct2]);

        $indexableProduct1->toArray()->willReturn(['id' => $identifiers[0], 'a key' => 'a value']);
        $indexableProduct2->toArray()->willReturn(['id' => $identifiers[1], 'a key' => 'another value']);

        $productAndProductModelIndexClient->bulkIndexes([
            ['id' => $identifiers[0], 'a key' => 'a value'],
            ['id' => $identifiers[1], 'a key' => 'another value'],
        ], 'id', Refresh::enable())->shouldBeCalled();

        $this->indexFromProductIdentifiers($identifiers, ['index_refresh' => Refresh::enable()]);
    }
}
