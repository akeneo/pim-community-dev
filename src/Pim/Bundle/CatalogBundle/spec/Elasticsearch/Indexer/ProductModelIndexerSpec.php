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
use Pim\Component\Catalog\Normalizer\Indexing\ProductModel;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelIndexerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, Client $productClient, Client $productModelClient, Client $productAndProductModelClient)
    {
        $this->beConstructedWith(
            $normalizer,
            $productClient,
            $productModelClient,
            $productAndProductModelClient,
            'an_index_type_for_test_purpose'
        );
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
        $productModelClient,
        \stdClass $aWrongProductModel
    ) {
        $normalizer->normalize($aWrongProductModel, ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn([]);
        $productModelClient->index(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('index', [$aWrongProductModel]);
    }

    function it_throws_an_exception_when_attempting_to_bulk_index_a_product_model_without_an_id(
        $normalizer,
        $productModelClient,
        ProductModelInterface $productModel,
        \stdClass $aWrongProductModel
    ) {
        $normalizer->normalize($productModel, ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn(['id' => 'baz']);
        $normalizer->normalize($aWrongProductModel, ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn([]);

        $productModelClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('indexAll', [[$productModel, $aWrongProductModel]]);
    }

    function it_indexes_a_single_product_model(
        $normalizer,
        $productModelClient,
        $productAndProductModelClient,
        ProductModelInterface $productModel
    ) {
        $normalizer->normalize($productModel, ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn(['id' => 'foobar', 'a key' => 'a value']);
        $productModelClient->index('an_index_type_for_test_purpose', 'foobar', ['id' => 'foobar', 'a key' => 'a value'])
            ->shouldBeCalled();

        $normalizer->normalize($productModel, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foobar', 'a key' => 'a value']);
        $productAndProductModelClient->index('an_index_type_for_test_purpose', 'foobar', ['id' => 'foobar', 'a key' => 'a value'])
            ->shouldBeCalled();

        $this->index($productModel);
    }

    function it_bulk_indexes_product_models(
        $normalizer,
        $productModelClient,
        $productAndProductModelClient,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $normalizer->normalize($productModel1, ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($productModel2, ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productModelClient->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $normalizer->normalize($productModel1, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($productModel2, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productAndProductModelClient->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $this->indexAll([$productModel1, $productModel2]);
    }

    function it_does_not_bulk_index_empty_arrays_of_product_models($normalizer, $productModelClient, $productAndProductModelClient)
    {
        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $productModelClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();
        $productAndProductModelClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->indexAll([]);
    }

    function it_deletes_product_models_from_elasticsearch_index($productClient, $productModelClient, $productAndProductModelClient)
    {
        $productModelClient->delete('an_index_type_for_test_purpose', '40')->shouldBeCalled();
        $productAndProductModelClient->delete('an_index_type_for_test_purpose', 'product_model_40')->shouldBeCalled();

        $productClient->deleteByQuery([
            'query' => [
                'term' => [
                    'ancestors.ids' => 'product_model_40',
                ],
            ],
        ])->shouldBeCalled();

        $productModelClient->deleteByQuery([
            'query' => [
                'term' => [
                    'ancestors.ids' => 'product_model_40',
                ],
            ],
        ])->shouldBeCalled();

        $productAndProductModelClient->deleteByQuery([
            'query' => [
                'term' => [
                    'ancestors.ids' => 'product_model_40',
                ],
            ],
        ])->shouldBeCalled();

        $this->remove(40)->shouldReturn(null);
    }

    function it_bulk_deletes_product_models_from_elasticsearch_index($productModelClient, $productAndProductModelClient)
    {
        $productModelClient->bulkDelete('an_index_type_for_test_purpose', ['40', '33'])
            ->shouldBeCalled();
        $productAndProductModelClient->bulkDelete('an_index_type_for_test_purpose', ['product_model_40', 'product_model_33'])
            ->shouldBeCalled();

        $this->removeAll([40, 33])->shouldReturn(null);
    }

    function it_indexes_product_models_and_wait_for_index_refresh_by_default(
        $normalizer,
        $productModelClient,
        $productAndProductModelClient,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $normalizer->normalize($productModel1, ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($productModel2, ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productModelClient->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $normalizer->normalize($productModel1, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($productModel2, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productAndProductModelClient->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $this->indexAll([$productModel1, $productModel2]);
    }

    function it_indexes_product_models_and_disable_index_refresh(
        $normalizer,
        $productModelClient,
        $productAndProductModelClient,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $normalizer->normalize($productModel1, ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($productModel2, ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productModelClient->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $normalizer->normalize($productModel1, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($productModel2, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productAndProductModelClient->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $this->indexAll([$productModel1, $productModel2], ['index_refresh' => Refresh::disable()]);
    }

    function it_indexes_product_models_and_enable_index_refresh_without_waiting_for_it(
        $normalizer,
        $productModelClient,
        $productAndProductModelClient,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $normalizer->normalize($productModel1, ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($productModel2, ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productModelClient->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $normalizer->normalize($productModel1, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($productModel2, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productAndProductModelClient->bulkIndexes('an_index_type_for_test_purpose', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $this->indexAll([$productModel1, $productModel2], ['index_refresh' => Refresh::disable()]);
    }
}
