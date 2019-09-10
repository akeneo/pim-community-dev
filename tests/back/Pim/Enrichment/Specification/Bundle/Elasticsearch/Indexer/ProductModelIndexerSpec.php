<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductModelRepository;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductModelIndexerInterface;
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

    function it_is_a_product_model_indexer()
    {
        $this->shouldImplement(ProductModelIndexerInterface::class);
    }

    function it_indexes_a_single_product_model(
        $normalizer,
        $productAndProductModelClient,
        $productModelRepository,
        ProductModelInterface $productModel
    ) {
        $code = 'foobar';
        $productModelRepository->findOneByIdentifier($code)->willReturn($productModel);
        $normalizer->normalize($productModel, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 1, 'code' => $code, 'a key' => 'a value']);
        $productAndProductModelClient->bulkIndexes(
            [['id' => 1, 'code' => $code, 'a key' => 'a value']],
            'id',
            Refresh::disable()
        )->shouldBeCalled();

        $this->indexFromProductModelCode($code);
    }

    function it_does_not_index_anything_if_identifier_is_unknown(
        $normalizer,
        $productAndProductModelClient,
        $productModelRepository,
        ProductModelInterface $product
    ) {
        $code = 'foobar';
        $productModelRepository->findOneByIdentifier($code)->willReturn(null);
        $normalizer->normalize(null, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldNotBeCalled();
        $productAndProductModelClient->index( $code, ['id' => 1, 'code' => $code, 'a key' => 'a value'])
            ->shouldNotBeCalled();
        $productAndProductModelClient->bulkIndexes(
            [['id' => 1, 'code' => $code, 'a key' => 'a value']],
            'id',
            Refresh::disable()
        )->shouldNotBeCalled();

        $this->indexFromProductModelCode($code);
    }

    function it_bulk_indexes_product_models(
        $normalizer,
        $productAndProductModelClient,
        $productModelRepository,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $code1 = 'foo';
        $code2 = 'bar';
        $code3 = 'baz';
        $productModelRepository->findOneByIdentifier($code1)->willReturn($productModel1);
        $productModelRepository->findOneByIdentifier($code2)->willReturn($productModel2);
        $productModelRepository->findOneByIdentifier($code3)->willReturn(null);
        $normalizer->normalize($productModel1, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 1, 'code' => $code1, 'a key' => 'a value']);
        $normalizer->normalize($productModel2, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 2, 'code' => $code2, 'a key' => 'another value']);

        $productAndProductModelClient->bulkIndexes([
            ['id' => 1, 'code' => $code1, 'a key' => 'a value'],
            ['id' => 2, 'code' => $code2, 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $this->indexFromProductModelCodes([$code1, $code2, $code3]);
    }

    function it_does_not_bulk_index_empty_arrays_of_product_models($normalizer, $productAndProductModelClient)
    {
        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $productAndProductModelClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->indexFromProductModelCodes([]);
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
        $normalizer,
        $productAndProductModelClient,
        $productModelRepository,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $code1 = 'foo';
        $code2 = 'bar';
        $code3 = 'baz';
        $productModelRepository->findOneByIdentifier($code1)->willReturn($productModel1);
        $productModelRepository->findOneByIdentifier($code2)->willReturn($productModel2);
        $productModelRepository->findOneByIdentifier($code3)->willReturn(null);
        $normalizer->normalize($productModel1, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 1, 'code' => $code1, 'a key' => 'a value']);
        $normalizer->normalize($productModel2, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 2, 'code' => $code2, 'a key' => 'another value']);

        $productAndProductModelClient->bulkIndexes([
            ['id' => 1, 'code' => $code1, 'a key' => 'a value'],
            ['id' => 2, 'code' => $code2, 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $this->indexFromProductModelCodes([$code1, $code2, $code3], ['index_refresh' => Refresh::disable()]);
    }

    function it_indexes_product_models_and_enable_index_refresh_without_waiting_for_it(
        $normalizer,
        $productAndProductModelClient,
        $productModelRepository,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $code1 = 'foo';
        $code2 = 'bar';
        $code3 = 'baz';
        $productModelRepository->findOneByIdentifier($code1)->willReturn($productModel1);
        $productModelRepository->findOneByIdentifier($code2)->willReturn($productModel2);
        $productModelRepository->findOneByIdentifier($code3)->willReturn(null);
        $normalizer->normalize($productModel1, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 1, 'code' => $code1, 'a key' => 'a value']);
        $normalizer->normalize($productModel2, ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 2, 'code' => $code2, 'a key' => 'another value']);

        $productAndProductModelClient->bulkIndexes([
            ['id' => 1, 'code' => $code1, 'a key' => 'a value'],
            ['id' => 2, 'code' => $code2, 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $this->indexFromProductModelCodes([$code1, $code2, $code3], ['index_refresh' => Refresh::waitFor()]);
    }
}
