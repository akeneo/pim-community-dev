<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductIndexerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        Client $productAndProductModelIndexClient,
        ProductRepositoryInterface $productRepository
    )     {
        $this->beConstructedWith(
            $normalizer,
            $productAndProductModelIndexClient,
            $productRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductIndexer::class);
    }

    function it_is_an_indexer()
    {
        $this->shouldImplement(IndexerInterface::class);
        $this->shouldImplement(BulkIndexerInterface::class);
        $this->shouldImplement(ProductIndexerInterface::class);
    }

    function it_is_a_index_remover()
    {
        $this->shouldImplement(RemoverInterface::class);
        $this->shouldImplement(BulkRemoverInterface::class);
    }

    function it_throws_an_exception_when_attempting_to_index_a_product_without_id(
        $normalizer,
        $productAndProductModelIndexClient,
        \stdClass $aWrongProduct
    ) {
        $normalizer->normalize($aWrongProduct, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->willReturn([]);
        $productAndProductModelIndexClient->index(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('index', [$aWrongProduct]);
    }

    function it_throws_an_exception_when_attempting_to_bulk_index_a_product_without_an_id(
        $normalizer,
        $productAndProductModelIndexClient,
        ProductInterface $product,
        \stdClass $aWrongProduct
    ) {
        $normalizer->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'baz']);
        $normalizer->normalize($aWrongProduct, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn([]);

        $productAndProductModelIndexClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('indexAll', [[$product, $aWrongProduct]]);
    }

    function it_indexes_a_single_product($normalizer, $productAndProductModelIndexClient, ProductInterface $product)
    {
        $normalizer->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foobar', 'a key' => 'a value']);
        $productAndProductModelIndexClient->index('', 'foobar', ['id' => 'foobar', 'a key' => 'a value'])
            ->shouldBeCalled();

        $this->index($product);
    }

    function it_indexes_a_single_product_from_identifier(
        $normalizer,
        $productAndProductModelIndexClient,
        $productRepository,
        ProductInterface $product
    ) {
        $identifier = 'foobar';
        $productRepository->findOneByIdentifier($identifier)->willReturn($product);
        $normalizer
            ->normalize($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => $identifier, 'a key' => 'a value']);
        $productAndProductModelIndexClient
            ->index('', $identifier, ['id' => $identifier, 'a key' => 'a value'])
            ->shouldBeCalled();

        $this->indexFromProductIdentifier($identifier);
    }

    function it_does_not_index_anything_if_identifier_is_unknown(
        $normalizer,
        $productAndProductModelIndexClient,
        $productRepository,
        ProductInterface $product
    ) {
        $identifier = 'foobar';
        $productRepository->findOneByIdentifier($identifier)->willReturn(null);
        $normalizer
            ->normalize(null, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldNotBeCalled();
        $productAndProductModelIndexClient
            ->index('', $identifier, ['id' => $identifier, 'a key' => 'a value'])
            ->shouldNotBeCalled();

        $this->indexFromProductIdentifier($identifier);
    }

    function it_bulk_indexes_products(
        $normalizer,
        $productAndProductModelIndexClient,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $normalizer->normalize($product1, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productAndProductModelIndexClient->bulkIndexes('', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $this->indexAll([$product1, $product2]);
    }

    function it_bulk_indexes_products_from_identifiers(
        $normalizer,
        $productAndProductModelIndexClient,
        $productRepository,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $identifiers = ['foo', 'bar', 'unknown'];

        $productRepository->findOneByIdentifier($identifiers[0])->willReturn($product1);
        $productRepository->findOneByIdentifier($identifiers[1])->willReturn($product2);
        $productRepository->findOneByIdentifier($identifiers[2])->willReturn(null);

        $normalizer->normalize($product1, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => $identifiers[0], 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => $identifiers[1], 'a key' => 'another value']);
        $normalizer->normalize(null, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldNotBeCalled();

        $productAndProductModelIndexClient->bulkIndexes('', [
            ['id' => $identifiers[0], 'a key' => 'a value'],
            ['id' => $identifiers[1], 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $this->indexFromProductIdentifiers($identifiers);
    }

    function it_does_not_bulk_index_empty_arrays_of_products($normalizer, $productAndProductModelIndexClient)
    {
        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $productAndProductModelIndexClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->indexAll([]);
    }

    function it_does_not_bulk_index_empty_arrays_of_identifiers(
        $normalizer,
        $productAndProductModelIndexClient,
        $productRepository
    )     {
        $productRepository->findOneByIdentifier(Argument::cetera())->shouldNotBeCalled();
        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $productAndProductModelIndexClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->indexFromProductIdentifiers([]);
    }

    function it_deletes_products_from_elasticsearch_index($productAndProductModelIndexClient)
    {
        $productAndProductModelIndexClient->delete('', 'product_40')->shouldBeCalled();

        $this->removeFromProductIdentifier(40)->shouldReturn(null);
    }

    function it_bulk_deletes_products_from_elasticsearch_index($productAndProductModelIndexClient)
    {
        $productAndProductModelIndexClient->bulkDelete('', ['product_40', 'product_33'])->shouldBeCalled();

        $this->removeManyFromProductIdentifiers([40, 33])->shouldReturn(null);
    }

    function it_indexes_products_and_waits_for_index_refresh(
        ProductInterface $product1,
        ProductInterface $product2,
        $normalizer,
        $productAndProductModelIndexClient
        ) {
        $normalizer->normalize($product1, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productAndProductModelIndexClient->bulkIndexes('', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $this->indexAll([$product1, $product2], ['index_refresh' => Refresh::waitFor()]);
    }

    function it_indexes_products_from_identifiers_and_waits_for_index_refresh(
        $normalizer,
        $productAndProductModelIndexClient,
        $productRepository,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $identifiers = ['foo', 'bar', 'unknown'];

        $productRepository->findOneByIdentifier($identifiers[0])->willReturn($product1);
        $productRepository->findOneByIdentifier($identifiers[1])->willReturn($product2);
        $productRepository->findOneByIdentifier($identifiers[2])->willReturn(null);

        $normalizer->normalize($product1, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => $identifiers[0], 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => $identifiers[1], 'a key' => 'another value']);
        $normalizer->normalize(null, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldNotBeCalled();

        $productAndProductModelIndexClient->bulkIndexes('', [
            ['id' => $identifiers[0], 'a key' => 'a value'],
            ['id' => $identifiers[1], 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $this->indexFromProductIdentifiers($identifiers, ['index_refresh' => Refresh::waitFor()]);
    }

    function it_indexes_products_and_disables_index_refresh_by_default(
        ProductInterface $product1,
        ProductInterface $product2,
        $normalizer,
        $productAndProductModelIndexClient
        ) {

        $normalizer->normalize($product1, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productAndProductModelIndexClient->bulkIndexes('', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $this->indexAll([$product1, $product2], ['index_refresh' => Refresh::disable()]);
    }

    function it_indexes_products_from_identifiers_and_disables_index_refresh_by_default(
        $normalizer,
        $productAndProductModelIndexClient,
        $productRepository,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $identifiers = ['foo', 'bar', 'unknown'];

        $productRepository->findOneByIdentifier($identifiers[0])->willReturn($product1);
        $productRepository->findOneByIdentifier($identifiers[1])->willReturn($product2);
        $productRepository->findOneByIdentifier($identifiers[2])->willReturn(null);

        $normalizer->normalize($product1, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => $identifiers[0], 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => $identifiers[1], 'a key' => 'another value']);
        $normalizer->normalize(null, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldNotBeCalled();

        $productAndProductModelIndexClient->bulkIndexes('', [
            ['id' => $identifiers[0], 'a key' => 'a value'],
            ['id' => $identifiers[1], 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $this->indexFromProductIdentifiers($identifiers, ['index_refresh' => Refresh::disable()]);
    }

    function it_indexes_products_and_enable_index_refresh_without_waiting_for_it(
        ProductInterface $product1,
        ProductInterface $product2,
        $normalizer,
        $productAndProductModelIndexClient
        ) {
        $normalizer->normalize($product1, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $productAndProductModelIndexClient->bulkIndexes('', [
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::enable())->shouldBeCalled();

        $this->indexAll([$product1, $product2], ['index_refresh' => Refresh::enable()]);
    }

    function it_indexes_products_from_identifiers_and_enable_index_refresh_without_waiting_for_it(
        $normalizer,
        $productAndProductModelIndexClient,
        $productRepository,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $identifiers = ['foo', 'bar', 'unknown'];

        $productRepository->findOneByIdentifier($identifiers[0])->willReturn($product1);
        $productRepository->findOneByIdentifier($identifiers[1])->willReturn($product2);
        $productRepository->findOneByIdentifier($identifiers[2])->willReturn(null);

        $normalizer->normalize($product1, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => $identifiers[0], 'a key' => 'a value']);
        $normalizer->normalize($product2, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['id' => $identifiers[1], 'a key' => 'another value']);
        $normalizer->normalize(null, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldNotBeCalled();

        $productAndProductModelIndexClient->bulkIndexes('', [
            ['id' => $identifiers[0], 'a key' => 'a value'],
            ['id' => $identifiers[1], 'a key' => 'another value'],
        ], 'id', Refresh::enable())->shouldBeCalled();

        $this->indexFromProductIdentifiers($identifiers, ['index_refresh' => Refresh::enable()]);
    }
}
