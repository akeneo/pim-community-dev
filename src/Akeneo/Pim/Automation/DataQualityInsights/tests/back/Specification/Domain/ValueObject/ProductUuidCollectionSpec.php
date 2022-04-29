<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use PhpSpec\ObjectBehavior;

final class ProductUuidCollectionSpec extends ObjectBehavior
{
    public function it_can_be_construct_from_an_array_of_string()
    {
        $ids = [
            '6d125b99-d971-41d9-a264-b020cd486aee',
            'fef37e64-a963-47a9-b087-2cc67968f0a2',
        ];

        $this->beConstructedThrough('fromStrings', [$ids]);
        $this->toArray()->shouldHaveCount(2);

        $this->toArray()[0]->shouldBeAnInstanceOf(ProductUuid::class);
        $this->toArray()[1]->shouldBeAnInstanceOf(ProductUuid::class);
    }

    public function it_can_be_construct_from_an_array_of_productUuid()
    {
        $ids = [
            ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
            ProductUuid::fromString(('fef37e64-a963-47a9-b087-2cc67968f0a2')),
        ];

        $this->beConstructedThrough('fromProductUuids', [$ids]);
        $this->toArray()->shouldHaveCount(2);
    }

    public function it_throws_an_exception_if_the_product_id_is_not_string_when_using_fromStrings()
    {
        $ids = [
            '6d125b99-d971-41d9-a264-b020cd486aee',
            12,
        ];

        $this->beConstructedThrough('fromStrings', [$ids]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_the_product_id_is_not_productUuid_class_when_using_fromProductUuids()
    {
        $uuids = [
            ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
            57,
        ];

        $this->beConstructedThrough('fromProductUuids', [$uuids]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_instantiates_with_unique_values_with_fromProductUuids()
    {
        $uuids = [
            ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
            ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
            ProductUuid::fromString(('6d125b99-d971-41d9-a264-b020cd486aee')),
            ProductUuid::fromString(('6d125b99-d971-41d9-a264-b020cd486aee')),
            ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
        ];
        $this->beConstructedThrough('fromProductUuids', [$uuids]);

        $this->toArray()->shouldBeLike([
            ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
            ProductUuid::fromString(('6d125b99-d971-41d9-a264-b020cd486aee'))
        ]);
    }

    public function it_gets_collection_as_an_array_of_ProductUuid()
    {
        $uuids = [
            ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
            ProductUuid::fromString(('fef37e64-a963-47a9-b087-2cc67968f0a2')),
            ProductUuid::fromString(('6d125b99-d971-41d9-a264-b020cd486aee')),
            ProductUuid::fromString(('b492b9f5-9a8f-495a-8cd7-912c69c31902'))
        ];
        $this->beConstructedThrough('fromProductUuids', [$uuids]);

        $this->toArray()->shouldBeArray();
        $this->toArray()->shouldBeLike($uuids);
    }

    public function it_gets_collection_as_an_array_of_string()
    {
        $uuids = [
            ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
            ProductUuid::fromString(('fef37e64-a963-47a9-b087-2cc67968f0a2')),
            ProductUuid::fromString(('6d125b99-d971-41d9-a264-b020cd486aee')),
            ProductUuid::fromString(('b492b9f5-9a8f-495a-8cd7-912c69c31902'))
        ];
        $uuidsExpected = [
            'df470d52-7723-4890-85a0-e79be625e2ed',
            'fef37e64-a963-47a9-b087-2cc67968f0a2',
            '6d125b99-d971-41d9-a264-b020cd486aee',
            'b492b9f5-9a8f-495a-8cd7-912c69c31902'
        ];
        $this->beConstructedThrough('fromProductUuids', [$uuids]);-

        $this->toArrayString()->shouldBeArray();
        $this->toArrayString()->shouldBeLike($uuidsExpected);
    }

    public function it_counts_product_id_element()
    {
        $ids = [
            '6d125b99-d971-41d9-a264-b020cd486aee',
            'fef37e64-a963-47a9-b087-2cc67968f0a2',
        ];

        $this->beConstructedThrough('fromStrings', [$ids]);
        $this->shouldHaveCount(2);
    }
}
