<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch\Indexer;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\Indexer\ProductIndexer;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductIndexerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, Client $productIndexClient, Client $productModelIndexClient)
    {
        $this->beConstructedWith($normalizer, $productIndexClient, $productModelIndexClient, 'an_index_type_for_test_purpose');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductIndexer::class);
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

    function it_throws_an_exception_when_attempting_to_index_a_product_without_id(
        $normalizer,
        $productIndexClient,
        $productModelIndexClient,
        \stdClass $aWrongProduct
    ) {
        $normalizer->normalize($aWrongProduct, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->willReturn([]);
        $productIndexClient->index(Argument::cetera())->shouldNotBeCalled();

        $normalizer->normalize($aWrongProduct, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldNotBeCalled([]);
        $productModelIndexClient->index(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('index', [$aWrongProduct]);
    }

    function it_throws_an_exception_when_attempting_to_bulk_index_a_product_without_an_id(
        $normalizer,
        $productIndexClient,
        $productModelIndexClient,
        ProductInterface $product,
        \stdClass $aWrongProduct
    ) {
        $normalizer->normalize($product, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'baz']);
        $normalizer->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'baz']);

        $normalizer->normalize($aWrongProduct, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn([]);
        $normalizer->normalize($aWrongProduct, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldNotBeCalled();

        $productIndexClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();
        $productModelIndexClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('indexAll', [[$product, $aWrongProduct]]);
    }

    function it_indexes_a_single_product($normalizer, $productIndexClient, $productModelIndexClient, ProductInterface $product)
    {
        $normalizer->normalize($product, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'foobar', 'a key' => 'a value']);
        $productIndexClient->index('an_index_type_for_test_purpose', 'foobar', ['id' => 'foobar', 'a key' => 'a value'])
            ->shouldBeCalled();

        $normalizer->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foobar', 'a key' => 'a value']);
        $productModelIndexClient->index('an_index_type_for_test_purpose', 'foobar', ['id' => 'foobar', 'a key' => 'a value'])
            ->shouldBeCalled();

        $this->index($product);
    }

    function it_bulk_indexes_products(
        $normalizer,
        $productIndexClient,
        $productModelIndexClient,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $normalizer->normalize($product1, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productIndexClient->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $normalizer->normalize($product1, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productModelIndexClient->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $this->indexAll([$product1, $product2]);
    }

    function it_does_not_bulk_index_empty_arrays_of_products($normalizer, $productIndexClient, $productModelIndexClient)
    {
        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $productIndexClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();
        $productModelIndexClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->indexAll([]);
    }

    function it_deletes_products_from_elasticsearch_index($productIndexClient, $productModelIndexClient)
    {
        $productIndexClient->delete('an_index_type_for_test_purpose', 40)->shouldBeCalled();
        $productModelIndexClient->delete('an_index_type_for_test_purpose', 'product_40')->shouldBeCalled();

        $this->remove(40)->shouldReturn(null);
    }

    function it_bulk_deletes_products_from_elasticsearch_index($productIndexClient, $productModelIndexClient)
    {
        $productIndexClient->bulkDelete('an_index_type_for_test_purpose', [40, 33])->shouldBeCalled();
        $productModelIndexClient->bulkDelete('an_index_type_for_test_purpose', ['product_40', 'product_33'])->shouldBeCalled();

        $this->removeAll([40, 33])->shouldReturn(null);
    }

    function it_indexes_products_and_wait_for_index_refresh_by_default(
        ProductInterface $product1,
        ProductInterface $product2,
        $normalizer,
        $productIndexClient,
        $productModelIndexClient
        ) {

        $normalizer->normalize($product1, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $normalizer->normalize($product1, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productIndexClient->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $productModelIndexClient->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $this->indexAll([$product1, $product2]);
    }

    function it_indexes_products_and_disable_index_refresh(
        ProductInterface $product1,
        ProductInterface $product2,
        $normalizer,
        $productIndexClient,
        $productModelIndexClient
        ) {

        $normalizer->normalize($product1, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $normalizer->normalize($product1, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productIndexClient->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $productModelIndexClient->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $this->indexAll([$product1, $product2], ["index_refresh" => Refresh::disable()]);
    }

    function it_indexes_products_and_enable_index_refresh_without_waiting_for_it(
        ProductInterface $product1,
        ProductInterface $product2,
        $normalizer,
        $productIndexClient,
        $productModelIndexClient
        ) {

        $normalizer->normalize($product1, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $normalizer->normalize($product1, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productIndexClient->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::enable())->shouldBeCalled();

        $productModelIndexClient->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::enable())->shouldBeCalled();

        $this->indexAll([$product1, $product2], ["index_refresh" => Refresh::enable()]);
    }
}
