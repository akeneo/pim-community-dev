<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Pim\Bundle\CatalogBundle\Elasticsearch\ProductIndexer;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductIndexerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductIndexer::class);
    }

    function let(NormalizerInterface $normalizer, Client $indexer)
    {
        $this->beConstructedWith($normalizer, $indexer, 'an_index_type_for_test_purpose');
    }

    function it_is_an_indexer()
    {
        $this->shouldImplement(IndexerInterface::class);
        $this->shouldImplement(BulkIndexerInterface::class);
    }

    function it_is_a_index_remover()
    {
        $this->shouldImplement(RemoverInterface::class);
        $this->shouldImplement(BulkRemoverInterface::class);
    }

    function it_throws_an_exception_when_attempting_to_index_a_non_product(
        $normalizer,
        $indexer,
        \stdClass $aWrongProduct
    ) {
        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $indexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('index', [$aWrongProduct]);
    }

    function it_throws_an_exception_when_attempting_to_index_a_product_without_id(
        $normalizer,
        $indexer,
        ProductInterface $aWrongProduct
    ) {
        $normalizer->normalize(Argument::cetera())->willReturn([]);
        $indexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('index', [$aWrongProduct]);
    }

    function it_throws_an_exception_when_attempting_to_bulk_index_a_non_product(
        $normalizer,
        $indexer,
        ProductInterface $product,
        \stdClass $aWrongProduct
    ) {
        $normalizer->normalize($product, Argument::cetera())->willReturn(['id' => 'baz']);
        $normalizer->normalize($aWrongProduct, Argument::cetera())->shouldNotBeCalled();
        $indexer->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('indexAll', [[$product, $aWrongProduct]]);
    }

    function it_throws_an_exception_when_attempting_to_bulk_index_a_product_without_an_id(
        $normalizer,
        $indexer,
        ProductInterface $product,
        ProductInterface $aWrongProduct
    ) {
        $normalizer->normalize($product, Argument::cetera())->willReturn(['id' => 'baz']);
        $normalizer->normalize($aWrongProduct, Argument::cetera())->willReturn([]);
        $indexer->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('indexAll', [[$product, $aWrongProduct]]);
    }

    function it_indexes_a_single_product($normalizer, $indexer, ProductInterface $product)
    {
        $normalizer->normalize($product, 'indexing')->willReturn(['id' => 'foobar', 'a key' => 'a value']);
        $indexer->index('an_index_type_for_test_purpose', 'foobar', ['id' => 'foobar', 'a key' => 'a value'])->shouldBeCalled();

        $this->index($product);
    }

    function it_bulk_indexes_products(
        $normalizer,
        $indexer,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $normalizer->normalize($product1, 'indexing')->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, 'indexing')->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $indexer->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value']
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $this->indexAll([$product1, $product2]);
    }

    function it_does_not_bulk_index_empty_arrays_of_products($normalizer, $indexer)
    {
        $indexer->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->indexAll([]);
    }

    function it_deletes_products_from_elasticsearch_index($indexer)
    {
        $indexer->delete('an_index_type_for_test_purpose', 40)->shouldBeCalled();

        $this->remove(40)->shouldReturn(null);
    }

    function it_bulk_deletes_products_from_elasticsearch_index($indexer)
    {
        $indexer->bulkDelete('an_index_type_for_test_purpose', [40, 33])->shouldBeCalled();

        $this->removeAll([40, 33])->shouldReturn(null);
    }
}
