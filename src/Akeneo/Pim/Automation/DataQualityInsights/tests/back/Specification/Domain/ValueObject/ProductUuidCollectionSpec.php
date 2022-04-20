<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

final class ProductUuidCollectionSpec extends ObjectBehavior
{
    // TODO
    public function it_can_be_construct_from_an_array_of_int()
    {
        $ids = [12, 57, 145, 67];

        $this->beConstructedThrough('fromInts', [$ids]);
        $this->toArray()->shouldHaveCount(4);

        $this->toArray()[0]->shouldBeAnInstanceOf(ProductUuid::class);
        $this->toArray()[1]->shouldBeAnInstanceOf(ProductUuid::class);
        $this->toArray()[2]->shouldBeAnInstanceOf(ProductUuid::class);
        $this->toArray()[3]->shouldBeAnInstanceOf(ProductUuid::class);
    }

    public function it_can_be_construct_from_an_array_of_string()
    {
        $ids = ['12', '57', '145', '67'];

        $this->beConstructedThrough('fromStrings', [$ids]);
        $this->toArray()->shouldHaveCount(4);

        $this->toArray()[0]->shouldBeAnInstanceOf(ProductUuid::class);
        $this->toArray()[1]->shouldBeAnInstanceOf(ProductUuid::class);
        $this->toArray()[2]->shouldBeAnInstanceOf(ProductUuid::class);
        $this->toArray()[3]->shouldBeAnInstanceOf(ProductUuid::class);
    }

    public function it_can_be_construct_from_an_array_of_productId()
    {
        $ids = [
            new ProductUuid(Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed')),
            new ProductUuid(Uuid::fromString('fef37e64-a963-47a9-b087-2cc67968f0a2')),
            new ProductUuid(Uuid::fromString('6d125b99-d971-41d9-a264-b020cd486aee')),
            new ProductUuid(Uuid::fromString('b492b9f5-9a8f-495a-8cd7-912c69c31902'))
        ];

        $this->beConstructedThrough('fromProductUuids', [$ids]);
        $this->toArray()->shouldHaveCount(4);
    }

    public function it_throws_an_exception_if_the_product_id_is_not_int_when_using_fromInts()
    {
        $ids = [12, 57, '145', 67];

        $this->beConstructedThrough('fromInts', [$ids]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_the_product_id_is_not_string_when_using_fromStrings()
    {
        $ids = ['12', 57, '145', '67'];

        $this->beConstructedThrough('fromStrings', [$ids]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_the_product_id_is_not_productId_class_when_using_fromProductIds()
    {
        $uuids = [new ProductUuid(Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed')), 57, new ProductUuid(Uuid::fromString('6d125b99-d971-41d9-a264-b020cd486aee')), '67'];

        $this->beConstructedThrough('fromProductIds', [$uuids]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_instantiates_with_unique_values_with_fromInts()
    {
        $ids = [12, 12, 67, 67];
        $this->beConstructedThrough('fromInts', [$ids]);

        $this->toArray()->shouldBeLike([new ProductUuid(12), new ProductUuid(67)]);
    }

    public function it_instantiates_with_unique_values_with_fromProductIds()
    {
        $uuids = [
            new ProductUuid(Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed')),
            new ProductUuid(Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed')),
            new ProductUuid(Uuid::fromString('6d125b99-d971-41d9-a264-b020cd486aee')),
            new ProductUuid(Uuid::fromString('6d125b99-d971-41d9-a264-b020cd486aee'))
        ];
        $this->beConstructedThrough('fromProductUuids', [$uuids]);

        $this->toArray()->shouldBeLike([
            new ProductUuid(Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed')),
            new ProductUuid(Uuid::fromString('6d125b99-d971-41d9-a264-b020cd486aee'))
        ]);
    }

    public function it_gets_collection_as_an_array_of_ProductUuid()
    {
        $uuids = [
            new ProductUuid(Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed')),
            new ProductUuid(Uuid::fromString('fef37e64-a963-47a9-b087-2cc67968f0a2')),
            new ProductUuid(Uuid::fromString('6d125b99-d971-41d9-a264-b020cd486aee')),
            new ProductUuid(Uuid::fromString('b492b9f5-9a8f-495a-8cd7-912c69c31902'))
        ];
        $this->beConstructedThrough('fromProductUuids', [$uuids]);

        $this->toArray()->shouldBeArray();
        $this->toArray()->shouldBeLike($uuids);
    }

    public function it_gets_collection_as_an_array_of_string()
    {
        $uuids = [
            new ProductUuid(Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed')),
            new ProductUuid(Uuid::fromString('fef37e64-a963-47a9-b087-2cc67968f0a2')),
            new ProductUuid(Uuid::fromString('6d125b99-d971-41d9-a264-b020cd486aee')),
            new ProductUuid(Uuid::fromString('b492b9f5-9a8f-495a-8cd7-912c69c31902'))
        ];
        $uuidsExpected = [
            'df470d52-7723-4890-85a0-e79be625e2ed',
            'fef37e64-a963-47a9-b087-2cc67968f0a2',
            '6d125b99-d971-41d9-a264-b020cd486aee',
            'b492b9f5-9a8f-495a-8cd7-912c69c31902'
        ];
        $this->beConstructedThrough('fromProductUuids', [$uuids]);

        $this->toArrayString()->shouldBeArray();
        $this->toArrayString()->shouldBeLike($uuidsExpected);
    }

    public function it_counts_product_id_element()
    {
        $ids = [1, 2, 3];
        $this->beConstructedThrough('fromInts', [$ids]);
        $this->shouldHaveCount(3);
    }
}
