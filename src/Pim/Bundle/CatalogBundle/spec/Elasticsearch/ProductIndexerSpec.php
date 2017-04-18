<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch;

use Akeneo\Bundle\ElasticsearchBundle\Client;
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
        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $indexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('index', [$aWrongProduct]);
    }

    function it_throws_an_exception_when_attempting_to_bulk_index_a_non_product(
        $normalizer,
        $indexer,
        ProductInterface $product,
        \stdClass $aWrongProduct
    ) {
        $product->getId()->willReturn(65);
        $normalizer->normalize(Argument::cetera())->shouldBeCalledTimes(1);
        $indexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('indexAll', [[$product, $aWrongProduct]]);
    }

    function it_throws_an_exception_when_attempting_to_bulk_index_a_product_without_an_id(
        $normalizer,
        $indexer,
        ProductInterface $product,
        ProductInterface $aWrongProduct
    ) {
        $product->getId()->willReturn(65);
        $normalizer->normalize(Argument::cetera())->shouldBeCalledTimes(1);
        $indexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('indexAll', [[$product, $aWrongProduct]]);
    }

    function it_indexes_a_single_product($normalizer, $indexer, ProductInterface $product)
    {
        $product->getId()->willReturn(78);

        $normalizer->normalize($product, 'indexing')->willReturn(['a key' => 'a value']);
        $indexer->index('an_index_type_for_test_purpose', 78, ['a key' => 'a value'])->shouldBeCalled();

        $this->index($product);
    }

    function it_bulk_indexes_products(
        $normalizer,
        $indexer,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $product1->getId()->willReturn(90);
        $product2->getId()->willReturn(56);

        $normalizer->normalize($product1, 'indexing')->willReturn(['a key' => 'a value']);
        $normalizer->normalize($product2, 'indexing')->willReturn(['a key' => 'another value']);

        $indexer->bulkIndexes('an_index_type_for_test_purpose', [
            ['a key' => 'a value'],
            ['a key' => 'another value']
        ], 'id')->shouldBeCalled();

        $this->indexAll([$product1, $product2]);
    }
}
