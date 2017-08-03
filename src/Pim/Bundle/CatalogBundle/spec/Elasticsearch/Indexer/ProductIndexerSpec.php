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
use Pim\Component\Catalog\Normalizer\Indexing\ProductModelNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductIndexerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, Client $productIndexer, Client $productModelIndexer)
    {
        $this->beConstructedWith($normalizer, $productIndexer, $productModelIndexer, 'an_index_type_for_test_purpose');
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
        $productIndexer,
        $productModelIndexer,
        \stdClass $aWrongProduct
    ) {
        $normalizer->normalize($aWrongProduct, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->willReturn([]);
        $productIndexer->index(Argument::cetera())->shouldNotBeCalled();

        $normalizer->normalize($aWrongProduct, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldNotBeCalled([]);
        $productModelIndexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('index', [$aWrongProduct]);
    }

    function it_throws_an_exception_when_attempting_to_bulk_index_a_product_without_an_id(
        $normalizer,
        $productIndexer,
        $productModelIndexer,
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

        $productIndexer->bulkIndexes(Argument::cetera())->shouldNotBeCalled();
        $productModelIndexer->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('indexAll', [[$product, $aWrongProduct]]);
    }

    function it_indexes_a_single_product($normalizer, $productIndexer, $productModelIndexer, ProductInterface $product)
    {
        $normalizer->normalize($product, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'foobar', 'a key' => 'a value']);
        $productIndexer->index('an_index_type_for_test_purpose', 'foobar', ['id' => 'foobar', 'a key' => 'a value'])
            ->shouldBeCalled();

        $normalizer->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foobar', 'a key' => 'a value']);
        $productModelIndexer->index('an_index_type_for_test_purpose', 'foobar', ['id' => 'foobar', 'a key' => 'a value'])
            ->shouldBeCalled();

        $this->index($product);
    }

    function it_bulk_indexes_products(
        $normalizer,
        $productIndexer,
        $productModelIndexer,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $normalizer->normalize($product1, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productIndexer->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $normalizer->normalize($product1, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productModelIndexer->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $this->indexAll([$product1, $product2]);
    }

    function it_does_not_bulk_index_empty_arrays_of_products($normalizer, $productIndexer, $productModelIndexer)
    {
        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $productIndexer->bulkIndexes(Argument::cetera())->shouldNotBeCalled();
        $productModelIndexer->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->indexAll([]);
    }

    function it_deletes_products_from_elasticsearch_index($productIndexer, $productModelIndexer)
    {
        $productIndexer->delete('an_index_type_for_test_purpose', 40)->shouldBeCalled();
        $productModelIndexer->delete('an_index_type_for_test_purpose', 40)->shouldBeCalled();

        $this->remove(40)->shouldReturn(null);
    }

    function it_bulk_deletes_products_from_elasticsearch_index($productIndexer, $productModelIndexer)
    {
        $productIndexer->bulkDelete('an_index_type_for_test_purpose', [40, 33])->shouldBeCalled();
        $productModelIndexer->bulkDelete('an_index_type_for_test_purpose', [40, 33])->shouldBeCalled();

        $this->removeAll([40, 33])->shouldReturn(null);
    }
}
