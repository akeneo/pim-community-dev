<?php

namespace Specification\Akeneo\Test\Pim\Enrichment\Product\Acceptance\InMemory;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Test\Pim\Enrichment\Product\Acceptance\InMemory\InMemoryGetProductUuids;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class InMemoryGetProductUuidsSpec extends ObjectBehavior
{
    private const FOO_UUID = 'de18ff6f-29a6-4b2a-9c38-0135aad32dbb';
    private const BAZ_UUID = 'a5ba5f6b-3307-4f44-8a98-0ac4d1370245';

    function let(ProductRepositoryInterface $productRepository)
    {
        $fooProduct = new Product(self::FOO_UUID);
        $productRepository->findOneByIdentifier('foo')->willReturn($fooProduct);
        $bazProduct = new Product(self::BAZ_UUID);
        $productRepository->findOneByIdentifier('baz')->willReturn($bazProduct);
        $productRepository->findOneByIdentifier(Argument::type('string'))->willReturn(null);

        $this->beConstructedWith($productRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryGetProductUuids::class);
    }

    function it_retrieves_a_product_uuid_from_an_identifier()
    {
        $this->fromIdentifier('foo')->shouldBeLike(Uuid::fromString(self::FOO_UUID));
    }

    function it_returns_null_if_identifier_does_not_exist()
    {
        $this->fromIdentifier('unknown')->shouldBe(null);
    }

    function it_retrieves_a_map_of_product_uuids_indexed_by_identifier()
    {
        $this->fromIdentifiers(['foo', 'bar', 'baz'])->shouldBeLike([
            'foo' => Uuid::fromString(self::FOO_UUID),
            'baz' => Uuid::fromString(self::BAZ_UUID),
        ]);
    }
}
