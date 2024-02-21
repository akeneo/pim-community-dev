<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Query\Cache;

use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuids;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class LRUCacheGetProductUuidsSpec extends ObjectBehavior
{
    public function let(GetProductUuids $getProductUuids)
    {
        $this->beConstructedWith($getProductUuids);
    }

    public function it_returns_a_single_uuid(GetProductUuids $getProductUuids): void
    {
        $uuid = Uuid::uuid4();
        $getProductUuids->fromIdentifier('product')->willReturn($uuid);
        $this->fromIdentifier('product')->shouldReturn($uuid);
    }

    public function it_uses_lru_cache_for_a_single_identifier(GetProductUuids $getProductUuids): void
    {
        $uuid = Uuid::uuid4();
        $getProductUuids->fromIdentifier('product')->shouldBeCalledOnce()->willReturn($uuid);
        $this->fromIdentifier('product');
        $this->fromIdentifier('product');
    }

    public function it_returns_multiple_uuids(GetProductUuids $getProductUuids): void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $getProductUuids->fromIdentifiers(['product1', 'product2'])->willReturn([
            'product1' => $uuid1,
            'product2' => $uuid2,
        ]);
        $this->fromIdentifiers(['product1', 'product2'])->shouldReturn([
            'product1' => $uuid1,
            'product2' => $uuid2,
        ]);
    }

    public function it_uses_lru_cache_for_multiple_uuids(GetProductUuids $getProductUuids): void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $expectedResult = [
            'product1' => $uuid1,
            'product2' => $uuid2,
        ];
        $getProductUuids->fromIdentifiers(['product1', 'product2'])->shouldBeCalledOnce()->willReturn($expectedResult);
        $this->fromIdentifiers(['product1', 'product2']);
        $this->fromIdentifiers(['product1', 'product2']);
    }

    public function it_uses_lru_cache_for_multiple_and_simple_uuids(GetProductUuids $getProductUuids): void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $expectedResult = [
            'product1' => $uuid1,
            'product2' => $uuid2,
        ];
        $getProductUuids->fromIdentifiers(['product1', 'product2'])->shouldBeCalledOnce()->willReturn($expectedResult);
        $getProductUuids->fromIdentifier(Argument::any())->shouldNotBeCalled();
        $this->fromIdentifiers(['product1', 'product2']);
        $this->fromIdentifier('product1');
        $this->fromIdentifier('product2');
        $this->fromIdentifiers(['product2', 'product1']);
    }

    function it_returns_null_when_product_does_not_exist(GetProductUuids $getProductUuids): void
    {
        $getProductUuids->fromIdentifier('non_existing_product')->willReturn(null);
        $this->fromIdentifier('non_existing_product')->shouldReturn(null);
    }

    function it_returns_null_when_products_do_not_exist(GetProductUuids $getProductUuids): void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $expectedResult = [
            'product1' => $uuid1,
            'product2' => $uuid2,
        ];
        $getProductUuids->fromIdentifiers(['product1', 'non_existing_product', 'product2'])->willReturn($expectedResult);
        $this->fromIdentifiers(['product1', 'non_existing_product', 'product2'])->shouldReturn($expectedResult);
    }
}
