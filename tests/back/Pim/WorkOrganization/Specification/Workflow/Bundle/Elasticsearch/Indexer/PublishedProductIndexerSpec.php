<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\PublishedProduct\PublishedProductNormalizer;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Prophecy\Argument;

class PublishedProductIndexerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        Client $publishedProductClient
    ) {
        $this->beConstructedWith($normalizer, $publishedProductClient);
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

    function it_throws_an_exception_when_attempting_to_index_a_published_product_without_id(
        $normalizer,
        $publishedProductClient,
        \stdClass $aWrongPublishedProduct
    ) {
        $normalizer->normalize($aWrongPublishedProduct, PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->willReturn([]);
        $publishedProductClient->index(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('index', [$aWrongPublishedProduct]);
    }

    function it_throws_an_exception_when_attempting_to_bulk_index_a_published_product_without_an_id(
        $normalizer,
        $publishedProductClient,
        PublishedProductInterface $publishedProduct,
        \stdClass $aWrongPublishedProduct
    ) {
        $normalizer->normalize($publishedProduct, PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'baz']);
        $normalizer->normalize($aWrongPublishedProduct, PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn([]);

        $publishedProductClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('indexAll', [[$publishedProduct, $aWrongPublishedProduct]]);
    }

    function it_indexes_a_single_published_product(
        $normalizer,
        $publishedProductClient,
        PublishedProductInterface $publishedProduct
    ) {
        $normalizer->normalize($publishedProduct, PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'foobar', 'a key' => 'a value']);
        $publishedProductClient->index('foobar', ['id' => 'foobar', 'a key' => 'a value'])
            ->shouldBeCalled();

        $this->index($publishedProduct);
    }

    function it_bulk_indexes_products(
        $normalizer,
        $publishedProductClient,
        PublishedProductInterface $publishedProduct1,
        PublishedProductInterface $publishedProduct2
    ) {
        $normalizer->normalize($publishedProduct1, PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($publishedProduct2, PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $publishedProductClient->bulkIndexes([
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::disable())->shouldBeCalled();

        $this->indexAll([$publishedProduct1, $publishedProduct2], ['index_refresh' => Refresh::disable()]);
    }

    function it_does_not_bulk_index_empty_arrays_of_products($normalizer, $publishedProductClient)
    {
        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $publishedProductClient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();

        $this->indexAll([]);
    }

    function it_deletes_products_from_elasticsearch_index($publishedProductClient)
    {
        $publishedProductClient->delete('40')->shouldBeCalled();

        $this->remove(40)->shouldReturn(null);
    }

    function it_bulk_deletes_products_from_elasticsearch_index($publishedProductClient)
    {
        $publishedProductClient->bulkDelete(['40', '33'])->shouldBeCalled();

        $this->removeAll([40, 33])->shouldReturn(null);
    }

    function it_indexes_products_and_waits_for_index_refresh(
        PublishedProductInterface $publishedProduct1,
        PublishedProductInterface $publishedProduct2,
        $normalizer,
        $publishedProductClient
    ) {
        $normalizer->normalize($publishedProduct1, PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($publishedProduct2, PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $publishedProductClient->bulkIndexes([
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::waitFor())->shouldBeCalled();

        $this->indexAll([$publishedProduct1, $publishedProduct2], ['index_refresh' => Refresh::waitFor()]);
    }

    function it_indexes_products_and_enable_index_refresh_without_waiting_for_it(
        PublishedProductInterface $publishedProduct1,
        PublishedProductInterface $publishedProduct2,
        $normalizer,
        $publishedProductClient
    ) {
        $normalizer->normalize($publishedProduct1, PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'foo', 'a key' => 'a value']);
        $normalizer->normalize($publishedProduct2, PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->willReturn(['id' => 'bar', 'a key' => 'another value']);

        $publishedProductClient->bulkIndexes([
            ['id' => 'foo', 'a key' => 'a value'],
            ['id' => 'bar', 'a key' => 'another value'],
        ], 'id', Refresh::enable())->shouldBeCalled();

        $this->indexAll([$publishedProduct1, $publishedProduct2], ['index_refresh' => Refresh::enable()]);
    }

}
