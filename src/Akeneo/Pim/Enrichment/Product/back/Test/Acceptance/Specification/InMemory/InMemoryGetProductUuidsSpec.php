<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory\InMemoryGetProductUuids;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class InMemoryGetProductUuidsSpec extends ObjectBehavior
{
    private const FOO_UUID = 'de18ff6f-29a6-4b2a-9c38-0135aad32dbb';
    private const BAZ_UUID = 'a5ba5f6b-3307-4f44-8a98-0ac4d1370245';

    function let(ProductRepositoryInterface $productRepository)
    {
        $fooProduct = new Product(self::FOO_UUID);
        $productRepository->findOneByIdentifier('foo')->willReturn($fooProduct);
        $productRepository->find(Argument::that(
            fn ($arg): bool => $arg instanceof UuidInterface && self::FOO_UUID === $arg->toString()
        ))->willReturn($fooProduct);
        $bazProduct = new Product(self::BAZ_UUID);
        $productRepository->findOneByIdentifier('baz')->willReturn($bazProduct);
        $productRepository->find(Argument::that(
            fn ($arg): bool => $arg instanceof UuidInterface && self::BAZ_UUID === $arg->toString()
        ))->willReturn($bazProduct);
        $productRepository->findOneByIdentifier(Argument::type('string'))->willReturn(null);
        $productRepository->find(Argument::any())->willReturn(null);

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

    function it_retrieves_an_existing_uuid()
    {
        $this->fromUuid(Uuid::fromString(self::FOO_UUID))->shouldBeLike(Uuid::fromString(self::FOO_UUID));
    }

    function it_returns_null_if_the_uuid_does_not_exist()
    {
        $this->fromUuid(Uuid::uuid4())->shouldBe(null);
    }

    function it_retrieves_existing_product_uuids()
    {
        $this->fromUuids([
            Uuid::uuid4(),
            Uuid::fromString(self::BAZ_UUID),
            Uuid::uuid4(),
            Uuid::fromString(self::FOO_UUID),
        ])->shouldBeLike([
            self::BAZ_UUID => Uuid::fromString(self::BAZ_UUID),
            self::FOO_UUID => Uuid::fromString(self::FOO_UUID),
        ]);
    }
}
