<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch\Indexer;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\Indexer\ProductModelIndexer;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelIndexerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, Client $productModelIndexer)
    {
        $this->beConstructedWith($normalizer, $productModelIndexer, 'an_index_type_for_test_purpose');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelIndexer::class);
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

    function it_throws_an_exception_when_attempting_to_index_a_product_model_without_id(
        $normalizer,
        $productModelIndexer,
        \stdClass $aWrongProductModel
    ) {
        $normalizer->normalize($aWrongProductModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn([]);
        $productModelIndexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('index', [$aWrongProductModel]);
    }

    function it_throws_an_exception_when_attempting_to_bulk_index_a_product_model_without_an_id(
        $normalizer,
        $productModelIndexer,
        ProductModelInterface $productModel,
        \stdClass $aWrongProductModel
    ) {
        $normalizer->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'baz']);
        $normalizer->normalize($aWrongProductModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn([]);

        $productModelIndexer->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('indexAll', [[$productModel, $aWrongProductModel]]);
    }

    function it_indexes_a_single_product_model($normalizer, $productModelIndexer, ProductModelInterface $productModel)
    {
        $normalizer->normalize($productModel, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foobar', 'a key' => 'a value']);
        $productModelIndexer->index('an_index_type_for_test_purpose', 'foobar', ['id' => 'foobar', 'a key' => 'a value'])
            ->shouldBeCalled();

        $this->index($productModel);
    }

    function it_bulk_indexes_product_models(
        $normalizer,
        $productIndexer,
        $productModelIndexer,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $normalizer->normalize($productModel1, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($productModel2, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productModelIndexer->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $this->indexAll([$productModel1, $productModel2]);
    }

    function it_does_not_bulk_index_empty_arrays_of_product_models($normalizer, $productModelIndexer)
    {
        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $productModelIndexer->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->indexAll([]);
    }

    function it_deletes_product_models_from_elasticsearch_index($productModelIndexer)
    {
        $productModelIndexer->delete('an_index_type_for_test_purpose', 40)->shouldBeCalled();

        $this->remove(40)->shouldReturn(null);
    }

    function it_bulk_deletes_product_models_from_elasticsearch_index($productModelIndexer)
    {
        $productModelIndexer->bulkDelete('an_index_type_for_test_purpose', [40, 33])->shouldBeCalled();

        $this->removeAll([40, 33])->shouldReturn(null);
    }
}
