<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use Doctrine\Common\Collections\ArrayCollection;
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
        ProductRepositoryInterface $productRepository,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        GetProductCompletenesses $getProductCompletenesses,
        EntityWithFamilyVariantAttributesProvider $attributesProvider
    ) {
        $this->beConstructedWith(
            $normalizer,
            $productAndProductModelIndexClient,
            $productRepository,
            $localeRepository,
            $channelRepository,
            $getProductCompletenesses,
            $attributesProvider
        );
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
        $normalizer,
        $productAndProductModelIndexClient,
        $productRepository,
        ProductInterface $product,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        GetProductCompletenesses $getProductCompletenesses
    ) {
        $identifier = 'foobar';
        $productRepository->findOneByIdentifier($identifier)->willReturn($product);

        $date = new \DateTime();
        $productValues = new WriteValueCollection([ScalarValue::value('a key', 'a value')]);

        $product->getId()->willReturn(12);
        $product->getIdentifier()->willReturn($identifier);
        $product->getCreated()->willReturn($date);
        $product->getUpdated()->willReturn($date);
        $product->isEnabled()->willReturn(true);
        $product->getFamily()->willReturn(null);
        $product->getValues()->willReturn($productValues);
        $product->getCategoryCodes()->willReturn([]);
        $product->getGroupCodes()->willReturn([]);
        $product->isVariant()->willReturn(false);
        $product->getAllAssociations()->willReturn(new ArrayCollection());
        $product->getParent()->willReturn(null);
        $product->getRawValues()->willReturn($productValues->toArray());

        $normalizer->normalize($productValues, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['a key' => 'a value']);

        $localeRepository->getActivatedLocaleCodes()->willReturn([]);
        $channelRepository->getChannelCodes()->willReturn([]);
        $productCompletenessCollection = new ProductCompletenessCollection(12, []);
        $getProductCompletenesses->fromProductId(12)->willReturn($productCompletenessCollection);

        $productAndProductModelIndexClient->bulkIndexes(
            ProductIndexer::INDEX_TYPE,
            [
                [
                    'id' => 'product_12',
                    'identifier' => $identifier,
                    'created' => $date->format('c'),
                    'updated' => $date->format('c'),
                    'family' => ['code' => null, 'labels' => null],
                    'enabled' => true,
                    'categories' => [],
                    'categories_of_ancestors' => [],
                    'groups' => [],
                    'completeness' => [],
                    'values' => ['a key' => 'a value'],
                    'ancestors' => ['ids' => [], 'codes' => [], 'labels' => []],
                    'label' => [],
                    'document_type' => ProductInterface::class,
                    'attributes_of_ancestors' => [],
                    'attributes_for_this_level' => ['a key-<all_channels>-<all_locales>']
                ],
            ],
            'id',
            Refresh::disable()
        )->shouldBeCalled();

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
            ->index(ProductIndexer::INDEX_TYPE, $identifier, ['id' => $identifier, 'a key' => 'a value'])
            ->shouldNotBeCalled();

        $this->indexFromProductIdentifier($identifier);
    }

    function it_bulk_indexes_products_from_identifiers(
        $normalizer,
        $productAndProductModelIndexClient,
        $productRepository,
        ProductInterface $product1,
        ProductInterface $product2,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        GetProductCompletenesses $getProductCompletenesses
    ) {
        $identifiers = ['foo', 'bar', 'unknown'];

        $productRepository->findOneByIdentifier($identifiers[0])->willReturn($product1);
        $productRepository->findOneByIdentifier($identifiers[1])->willReturn($product2);
        $productRepository->findOneByIdentifier($identifiers[2])->willReturn(null);

        $localeRepository->getActivatedLocaleCodes()->willReturn([]);
        $channelRepository->getChannelCodes()->willReturn([]);
        $productCompletenessCollection = new ProductCompletenessCollection(12, []);
        $getProductCompletenesses->fromProductId(12)->willReturn($productCompletenessCollection);
        $productCompletenessCollection2 = new ProductCompletenessCollection(14, []);
        $getProductCompletenesses->fromProductId(14)->willReturn($productCompletenessCollection2);

        $date = new \DateTime();
        $productValues = new WriteValueCollection([ScalarValue::value('a key', 'a value')]);

        $normalizer->normalize($productValues, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['a key' => 'a value']);

        $product1->getId()->willReturn(12);
        $product1->getIdentifier()->willReturn($identifiers[0]);
        $product1->getCreated()->willReturn($date);
        $product1->getUpdated()->willReturn($date);
        $product1->isEnabled()->willReturn(true);
        $product1->getFamily()->willReturn(null);
        $product1->getValues()->willReturn($productValues);
        $product1->getCategoryCodes()->willReturn([]);
        $product1->getGroupCodes()->willReturn([]);
        $product1->isVariant()->willReturn(false);
        $product1->getAllAssociations()->willReturn(new ArrayCollection());
        $product1->getParent()->willReturn(null);
        $product1->getRawValues()->willReturn($productValues->toArray());

        $product2->getId()->willReturn(14);
        $product2->getIdentifier()->willReturn($identifiers[1]);
        $product2->getCreated()->willReturn($date);
        $product2->getUpdated()->willReturn($date);
        $product2->isEnabled()->willReturn(true);
        $product2->getFamily()->willReturn(null);
        $product2->getValues()->willReturn($productValues);
        $product2->getCategoryCodes()->willReturn([]);
        $product2->getGroupCodes()->willReturn([]);
        $product2->isVariant()->willReturn(false);
        $product2->getAllAssociations()->willReturn(new ArrayCollection());
        $product2->getParent()->willReturn(null);
        $product2->getRawValues()->willReturn($productValues->toArray());

        $productAndProductModelIndexClient->bulkIndexes(ProductIndexer::INDEX_TYPE, [
            [
                'id' => 'product_12',
                'identifier' => $identifiers[0],
                'created' => $date->format('c'),
                'updated' => $date->format('c'),
                'family' => ['code' => null, 'labels' => null],
                'enabled' => true,
                'categories' => [],
                'categories_of_ancestors' => [],
                'groups' => [],
                'completeness' => [],
                'values' => ['a key' => 'a value'],
                'ancestors' => ['ids' => [], 'codes' => [], 'labels' => []],
                'label' => [],
                'document_type' => ProductInterface::class,
                'attributes_of_ancestors' => [],
                'attributes_for_this_level' => ['a key-<all_channels>-<all_locales>']
            ],
            [
                'id' => 'product_14',
                'identifier' => $identifiers[1],
                'created' => $date->format('c'),
                'updated' => $date->format('c'),
                'family' => ['code' => null, 'labels' => null],
                'enabled' => true,
                'categories' => [],
                'categories_of_ancestors' => [],
                'groups' => [],
                'completeness' => [],
                'values' => ['a key' => 'a value'],
                'ancestors' => ['ids' => [], 'codes' => [], 'labels' => []],
                'label' => [],
                'document_type' => ProductInterface::class,
                'attributes_of_ancestors' => [],
                'attributes_for_this_level' => ['a key-<all_channels>-<all_locales>']
            ],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $this->indexFromProductIdentifiers($identifiers);
    }

    function it_does_not_bulk_index_empty_arrays_of_identifiers(
        $normalizer,
        $productAndProductModelIndexClient,
        $productRepository
    ) {
        $productRepository->findOneByIdentifier(Argument::cetera())->shouldNotBeCalled();
        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $productAndProductModelIndexClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->indexFromProductIdentifiers([]);
    }

    function it_deletes_products_from_elasticsearch_index($productAndProductModelIndexClient)
    {
        $productAndProductModelIndexClient->delete(ProductIndexer::INDEX_TYPE, 'product_40')->shouldBeCalled();

        $this->removeFromProductId(40)->shouldReturn(null);
    }

    function it_bulk_deletes_products_from_elasticsearch_index($productAndProductModelIndexClient)
    {
        $productAndProductModelIndexClient->bulkDelete(ProductIndexer::INDEX_TYPE, ['product_40', 'product_33'])
            ->shouldBeCalled();

        $this->removeFromProductIds([40, 33])->shouldReturn(null);
    }

    function it_indexes_products_from_identifiers_and_waits_for_index_refresh(
        $normalizer,
        $productAndProductModelIndexClient,
        $productRepository,
        ProductInterface $product1,
        ProductInterface $product2,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        GetProductCompletenesses $getProductCompletenesses
    ) {
        $identifiers = ['foo', 'bar', 'unknown'];

        $productRepository->findOneByIdentifier($identifiers[0])->willReturn($product1);
        $productRepository->findOneByIdentifier($identifiers[1])->willReturn($product2);
        $productRepository->findOneByIdentifier($identifiers[2])->willReturn(null);

        $localeRepository->getActivatedLocaleCodes()->willReturn([]);
        $channelRepository->getChannelCodes()->willReturn([]);
        $productCompletenessCollection = new ProductCompletenessCollection(12, []);
        $getProductCompletenesses->fromProductId(12)->willReturn($productCompletenessCollection);
        $productCompletenessCollection = new ProductCompletenessCollection(14, []);
        $getProductCompletenesses->fromProductId(14)->willReturn($productCompletenessCollection);

        $date = new \DateTime();
        $productValues = new WriteValueCollection([ScalarValue::value('a key', 'a value')]);

        $normalizer->normalize($productValues, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['a key' => 'a value']);

        $product1->getId()->willReturn(12);
        $product1->getIdentifier()->willReturn($identifiers[0]);
        $product1->getCreated()->willReturn($date);
        $product1->getUpdated()->willReturn($date);
        $product1->isEnabled()->willReturn(true);
        $product1->getFamily()->willReturn(null);
        $product1->getValues()->willReturn($productValues);
        $product1->getCategoryCodes()->willReturn([]);
        $product1->getGroupCodes()->willReturn([]);
        $product1->isVariant()->willReturn(false);
        $product1->getAllAssociations()->willReturn(new ArrayCollection());
        $product1->getParent()->willReturn(null);
        $product1->getRawValues()->willReturn($productValues->toArray());

        $product2->getId()->willReturn(14);
        $product2->getIdentifier()->willReturn($identifiers[1]);
        $product2->getCreated()->willReturn($date);
        $product2->getUpdated()->willReturn($date);
        $product2->isEnabled()->willReturn(true);
        $product2->getFamily()->willReturn(null);
        $product2->getValues()->willReturn($productValues);
        $product2->getCategoryCodes()->willReturn([]);
        $product2->getGroupCodes()->willReturn([]);
        $product2->isVariant()->willReturn(false);
        $product2->getAllAssociations()->willReturn(new ArrayCollection());
        $product2->getParent()->willReturn(null);
        $product2->getRawValues()->willReturn($productValues->toArray());

        $productAndProductModelIndexClient->bulkIndexes(ProductIndexer::INDEX_TYPE, [
            [
                'id' => 'product_12',
                'identifier' => $identifiers[0],
                'created' => $date->format('c'),
                'updated' => $date->format('c'),
                'family' => ['code' => null, 'labels' => null],
                'enabled' => true,
                'categories' => [],
                'categories_of_ancestors' => [],
                'groups' => [],
                'completeness' => [],
                'values' => ['a key' => 'a value'],
                'ancestors' => ['ids' => [], 'codes' => [], 'labels' => []],
                'label' => [],
                'document_type' => ProductInterface::class,
                'attributes_of_ancestors' => [],
                'attributes_for_this_level' => ['a key-<all_channels>-<all_locales>']
            ],
            [
                'id' => 'product_14',
                'identifier' => $identifiers[1],
                'created' => $date->format('c'),
                'updated' => $date->format('c'),
                'family' => ['code' => null, 'labels' => null],
                'enabled' => true,
                'categories' => [],
                'categories_of_ancestors' => [],
                'groups' => [],
                'completeness' => [],
                'values' => ['a key' => 'a value'],
                'ancestors' => ['ids' => [], 'codes' => [], 'labels' => []],
                'label' => [],
                'document_type' => ProductInterface::class,
                'attributes_of_ancestors' => [],
                'attributes_for_this_level' => ['a key-<all_channels>-<all_locales>']
            ],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $this->indexFromProductIdentifiers($identifiers, ['index_refresh' => Refresh::waitFor()]);
    }

    function it_indexes_products_from_identifiers_and_disables_index_refresh_by_default(
        $normalizer,
        $productAndProductModelIndexClient,
        $productRepository,
        ProductInterface $product1,
        ProductInterface $product2,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        GetProductCompletenesses $getProductCompletenesses
    ) {
        $identifiers = ['foo', 'bar', 'unknown'];

        $productRepository->findOneByIdentifier($identifiers[0])->willReturn($product1);
        $productRepository->findOneByIdentifier($identifiers[1])->willReturn($product2);
        $productRepository->findOneByIdentifier($identifiers[2])->willReturn(null);

        $localeRepository->getActivatedLocaleCodes()->willReturn([]);
        $channelRepository->getChannelCodes()->willReturn([]);
        $productCompletenessCollection = new ProductCompletenessCollection(12, []);
        $getProductCompletenesses->fromProductId(12)->willReturn($productCompletenessCollection);
        $productCompletenessCollection = new ProductCompletenessCollection(14, []);
        $getProductCompletenesses->fromProductId(14)->willReturn($productCompletenessCollection);

        $date = new \DateTime();
        $productValues = new WriteValueCollection([ScalarValue::value('a key', 'a value')]);

        $normalizer->normalize($productValues, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['a key' => 'a value']);

        $product1->getId()->willReturn(12);
        $product1->getIdentifier()->willReturn($identifiers[0]);
        $product1->getCreated()->willReturn($date);
        $product1->getUpdated()->willReturn($date);
        $product1->isEnabled()->willReturn(true);
        $product1->getFamily()->willReturn(null);
        $product1->getValues()->willReturn($productValues);
        $product1->getCategoryCodes()->willReturn([]);
        $product1->getGroupCodes()->willReturn([]);
        $product1->isVariant()->willReturn(false);
        $product1->getAllAssociations()->willReturn(new ArrayCollection());
        $product1->getParent()->willReturn(null);
        $product1->getRawValues()->willReturn($productValues->toArray());

        $product2->getId()->willReturn(14);
        $product2->getIdentifier()->willReturn($identifiers[1]);
        $product2->getCreated()->willReturn($date);
        $product2->getUpdated()->willReturn($date);
        $product2->isEnabled()->willReturn(true);
        $product2->getFamily()->willReturn(null);
        $product2->getValues()->willReturn($productValues);
        $product2->getCategoryCodes()->willReturn([]);
        $product2->getGroupCodes()->willReturn([]);
        $product2->isVariant()->willReturn(false);
        $product2->getAllAssociations()->willReturn(new ArrayCollection());
        $product2->getParent()->willReturn(null);
        $product2->getRawValues()->willReturn($productValues->toArray());

        $productAndProductModelIndexClient->bulkIndexes(ProductIndexer::INDEX_TYPE, [
            [
                'id' => 'product_12',
                'identifier' => $identifiers[0],
                'created' => $date->format('c'),
                'updated' => $date->format('c'),
                'family' => ['code' => null, 'labels' => null],
                'enabled' => true,
                'categories' => [],
                'categories_of_ancestors' => [],
                'groups' => [],
                'completeness' => [],
                'values' => ['a key' => 'a value'],
                'ancestors' => ['ids' => [], 'codes' => [], 'labels' => []],
                'label' => [],
                'document_type' => ProductInterface::class,
                'attributes_of_ancestors' => [],
                'attributes_for_this_level' => ['a key-<all_channels>-<all_locales>']
            ],
            [
                'id' => 'product_14',
                'identifier' => $identifiers[1],
                'created' => $date->format('c'),
                'updated' => $date->format('c'),
                'family' => ['code' => null, 'labels' => null],
                'enabled' => true,
                'categories' => [],
                'categories_of_ancestors' => [],
                'groups' => [],
                'completeness' => [],
                'values' => ['a key' => 'a value'],
                'ancestors' => ['ids' => [], 'codes' => [], 'labels' => []],
                'label' => [],
                'document_type' => ProductInterface::class,
                'attributes_of_ancestors' => [],
                'attributes_for_this_level' => ['a key-<all_channels>-<all_locales>']
            ],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $this->indexFromProductIdentifiers($identifiers, ['index_refresh' => Refresh::disable()]);
    }

    function it_indexes_products_from_identifiers_and_enable_index_refresh_without_waiting_for_it(
        $normalizer,
        $productAndProductModelIndexClient,
        $productRepository,
        ProductInterface $product1,
        ProductInterface $product2,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        GetProductCompletenesses $getProductCompletenesses
    ) {
        $identifiers = ['foo', 'bar', 'unknown'];

        $productRepository->findOneByIdentifier($identifiers[0])->willReturn($product1);
        $productRepository->findOneByIdentifier($identifiers[1])->willReturn($product2);
        $productRepository->findOneByIdentifier($identifiers[2])->willReturn(null);

        $localeRepository->getActivatedLocaleCodes()->willReturn([]);
        $channelRepository->getChannelCodes()->willReturn([]);
        $productCompletenessCollection = new ProductCompletenessCollection(12, []);
        $getProductCompletenesses->fromProductId(12)->willReturn($productCompletenessCollection);
        $productCompletenessCollection = new ProductCompletenessCollection(14, []);
        $getProductCompletenesses->fromProductId(14)->willReturn($productCompletenessCollection);

        $date = new \DateTime();
        $productValues = new WriteValueCollection([ScalarValue::value('a key', 'a value')]);

        $normalizer->normalize($productValues, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->willReturn(['a key' => 'a value']);

        $product1->getId()->willReturn(12);
        $product1->getIdentifier()->willReturn($identifiers[0]);
        $product1->getCreated()->willReturn($date);
        $product1->getUpdated()->willReturn($date);
        $product1->isEnabled()->willReturn(true);
        $product1->getFamily()->willReturn(null);
        $product1->getValues()->willReturn($productValues);
        $product1->getCategoryCodes()->willReturn([]);
        $product1->getGroupCodes()->willReturn([]);
        $product1->isVariant()->willReturn(false);
        $product1->getAllAssociations()->willReturn(new ArrayCollection());
        $product1->getParent()->willReturn(null);
        $product1->getRawValues()->willReturn($productValues->toArray());

        $product2->getId()->willReturn(14);
        $product2->getIdentifier()->willReturn($identifiers[1]);
        $product2->getCreated()->willReturn($date);
        $product2->getUpdated()->willReturn($date);
        $product2->isEnabled()->willReturn(true);
        $product2->getFamily()->willReturn(null);
        $product2->getValues()->willReturn($productValues);
        $product2->getCategoryCodes()->willReturn([]);
        $product2->getGroupCodes()->willReturn([]);
        $product2->isVariant()->willReturn(false);
        $product2->getAllAssociations()->willReturn(new ArrayCollection());
        $product2->getParent()->willReturn(null);
        $product2->getRawValues()->willReturn($productValues->toArray());

        $productAndProductModelIndexClient->bulkIndexes(ProductIndexer::INDEX_TYPE, [
            [
                'id' => 'product_12',
                'identifier' => $identifiers[0],
                'created' => $date->format('c'),
                'updated' => $date->format('c'),
                'family' => ['code' => null, 'labels' => null],
                'enabled' => true,
                'categories' => [],
                'categories_of_ancestors' => [],
                'groups' => [],
                'completeness' => [],
                'values' => ['a key' => 'a value'],
                'ancestors' => ['ids' => [], 'codes' => [], 'labels' => []],
                'label' => [],
                'document_type' => ProductInterface::class,
                'attributes_of_ancestors' => [],
                'attributes_for_this_level' => ['a key-<all_channels>-<all_locales>']
            ],
            [
                'id' => 'product_14',
                'identifier' => $identifiers[1],
                'created' => $date->format('c'),
                'updated' => $date->format('c'),
                'family' => ['code' => null, 'labels' => null],
                'enabled' => true,
                'categories' => [],
                'categories_of_ancestors' => [],
                'groups' => [],
                'completeness' => [],
                'values' => ['a key' => 'a value'],
                'ancestors' => ['ids' => [], 'codes' => [], 'labels' => []],
                'label' => [],
                'document_type' => ProductInterface::class,
                'attributes_of_ancestors' => [],
                'attributes_for_this_level' => ['a key-<all_channels>-<all_locales>']
            ],
        ], 'id', Refresh::enable())->shouldBeCalled();

        $this->indexFromProductIdentifiers($identifiers, ['index_refresh' => Refresh::enable()]);
    }
}
