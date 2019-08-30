<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductModelRepository;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelIndexerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        Client $productAndProductModelClient,
        ProductModelRepository $productModelRepository
    ) {
        $this->beConstructedWith($normalizer, $productAndProductModelClient, $productModelRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelIndexer::class);
    }

    function it_is_an_indexer()
    {
        $this->shouldImplement(ProductIndexerInterface::class);
    }

    function it_indexes_a_single_product_model(
        $normalizer,
        $productAndProductModelClient,
        $productModelRepository,
        ProductModelInterface $productModel
    ) {
        $identifier = 'foobar';
        $productModelRepository->findOneByIdentifier($identifier)->willReturn($productModel);
        $normalizer->normalize($productModel, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => $identifier, 'a key' => 'a value']);
        $productAndProductModelClient->index(ProductModelIndexer::INDEX_TYPE, 'foobar', ['id' => $identifier, 'a key' => 'a value'])
            ->shouldBeCalled();

        $this->indexFromProductIdentifier($identifier);
    }

    function it_does_not_index_anything_if_identifier_is_unknown(
        $normalizer,
        $productAndProductModelClient,
        $productModelRepository,
        ProductModelInterface $product
    ) {
        $identifier = 'foobar';
        $productModelRepository->findOneByIdentifier($identifier)->willReturn(null);
        $normalizer->normalize(null, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldNotBeCalled();
        $productAndProductModelClient->index(ProductModelIndexer::INDEX_TYPE, $identifier, ['id' => $identifier, 'a key' => 'a value'])
            ->shouldNotBeCalled();

        $this->indexFromProductIdentifier($identifier);
    }

    function it_bulk_indexes_product_models(
        $normalizer,
        $productAndProductModelClient,
        $productModelRepository,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $identifier1 = 'foo';
        $identifier2 = 'bar';
        $identifier3 = 'baz';
        $productModelRepository->findOneByIdentifier($identifier1)->willReturn($productModel1);
        $productModelRepository->findOneByIdentifier($identifier2)->willReturn($productModel2);
        $productModelRepository->findOneByIdentifier($identifier3)->willReturn(null);
        $normalizer->normalize($productModel1, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => $identifier1, 'a key' => 'a value']);
        $normalizer->normalize($productModel2, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => $identifier2, 'a key' => 'another value']);

        $productAndProductModelClient->bulkIndexes(ProductModelIndexer::INDEX_TYPE, [
            ['id' => $identifier1, 'a key' => 'a value'],
            ['id' => $identifier2, 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $this->indexFromProductIdentifiers([$identifier1, $identifier2, $identifier3]);
    }

    function it_does_not_bulk_index_empty_arrays_of_product_models($normalizer, $productAndProductModelClient)
    {
        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $productAndProductModelClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->indexFromProductIdentifiers([]);
    }

    function it_deletes_product_models_from_elasticsearch_index($productAndProductModelClient)
    {
        $productAndProductModelClient->delete('pim_catalog_product', 'product_model_40')->shouldBeCalled();

        $productAndProductModelClient->deleteByQuery([
            'query' => [
                'term' => [
                    'ancestors.ids' => 'product_model_40',
                ],
            ],
        ])->shouldBeCalled();

        $this->removeFromProductId(40)->shouldReturn(null);
    }

    function it_bulk_deletes_product_models_from_elasticsearch_index($productAndProductModelClient)
    {
        $productAndProductModelClient->bulkDelete('pim_catalog_product', ['product_model_40', 'product_model_33'])
            ->shouldBeCalled();

        $this->removeManyFromProductIds([40, 33])->shouldReturn(null);
    }

    function it_indexes_product_models_and_disable_index_refresh(
        $normalizer,
        $productAndProductModelClient,
        $productModelRepository,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $identifier1 = 'foo';
        $identifier2 = 'bar';
        $identifier3 = 'baz';
        $productModelRepository->findOneByIdentifier($identifier1)->willReturn($productModel1);
        $productModelRepository->findOneByIdentifier($identifier2)->willReturn($productModel2);
        $productModelRepository->findOneByIdentifier($identifier3)->willReturn(null);
        $normalizer->normalize($productModel1, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => $identifier1, 'a key' => 'a value']);
        $normalizer->normalize($productModel2, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => $identifier2, 'a key' => 'another value']);

        $productAndProductModelClient->bulkIndexes(ProductModelIndexer::INDEX_TYPE, [
            ['id' => $identifier1, 'a key' => 'a value'],
            ['id' => $identifier2, 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $this->indexFromProductIdentifiers([$identifier1, $identifier2, $identifier3], ['index_refresh' => Refresh::disable()]);
    }

    function it_indexes_product_models_and_enable_index_refresh_without_waiting_for_it(
        $normalizer,
        $productAndProductModelClient,
        $productModelRepository,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $identifier1 = 'foo';
        $identifier2 = 'bar';
        $identifier3 = 'baz';
        $productModelRepository->findOneByIdentifier($identifier1)->willReturn($productModel1);
        $productModelRepository->findOneByIdentifier($identifier2)->willReturn($productModel2);
        $productModelRepository->findOneByIdentifier($identifier3)->willReturn(null);
        $normalizer->normalize($productModel1, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => $identifier1, 'a key' => 'a value']);
        $normalizer->normalize($productModel2, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => $identifier2, 'a key' => 'another value']);

        $productAndProductModelClient->bulkIndexes(ProductModelIndexer::INDEX_TYPE, [
            ['id' => $identifier1, 'a key' => 'a value'],
            ['id' => $identifier2, 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $this->indexFromProductIdentifiers([$identifier1, $identifier2, $identifier3], ['index_refresh' => Refresh::waitFor()]);
    }
}
