<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use PhpSpec\ObjectBehavior;

final class ProductIdCollectionSpec extends ObjectBehavior
{

    public function it_can_be_construct_from_an_array_of_int()
    {
        $ids = [12, 57, 145, 67];

        $this->beConstructedThrough('fromInts', [$ids]);
        $this->toArray()->shouldHaveCount(4);

        $this->toArray()[0]->shouldBeAnInstanceOf(ProductId::class);
        $this->toArray()[1]->shouldBeAnInstanceOf(ProductId::class);
        $this->toArray()[2]->shouldBeAnInstanceOf(ProductId::class);
        $this->toArray()[3]->shouldBeAnInstanceOf(ProductId::class);
    }

    public function it_can_be_construct_from_an_array_of_string()
    {
        $ids = ['12', '57', '145', '67'];

        $this->beConstructedThrough('fromStrings', [$ids]);
        $this->toArray()->shouldHaveCount(4);

        $this->toArray()[0]->shouldBeAnInstanceOf(ProductId::class);
        $this->toArray()[1]->shouldBeAnInstanceOf(ProductId::class);
        $this->toArray()[2]->shouldBeAnInstanceOf(ProductId::class);
        $this->toArray()[3]->shouldBeAnInstanceOf(ProductId::class);
    }

    public function it_can_be_construct_from_an_array_of_productId()
    {
        $ids = [new ProductId(12), new ProductId(57), new ProductId(145), new ProductId(67)];

        $this->beConstructedThrough('fromProductIds', [$ids]);
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
        $ids = [new ProductId(12), 57, new ProductId(145), '67'];

        $this->beConstructedThrough('fromProductIds', [$ids]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_instantiates_with_unique_values_with_fromInts()
    {
        $ids = [12, 12, 67, 67];
        $this->beConstructedThrough('fromInts', [$ids]);

        $this->toArray()->shouldBeLike([new ProductId(12), new ProductId(67)]);
    }

    public function it_instantiates_with_unique_values_with_fromProductIds()
    {
        $ids = [new ProductId(12), new ProductId(12), new ProductId(67), new ProductId(67)];
        $this->beConstructedThrough('fromProductIds', [$ids]);

        $this->toArray()->shouldBeLike([new ProductId(12), new ProductId(67)]);
    }

    public function it_gets_collection_as_an_array_of_ProductId()
    {
        $ids = [new ProductId(123), new ProductId(12), new ProductId(68), new ProductId(167)];
        $this->beConstructedThrough('fromProductIds', [$ids]);

        $this->toArray()->shouldBeArray();
        $this->toArray()->shouldBeLike($ids);
    }

    public function it_gets_collection_as_an_array_of_int()
    {
        $ids = [new ProductId(123), new ProductId(12), new ProductId(68), new ProductId(167)];
        $idsExpected = [123, 12, 68, 167];
        $this->beConstructedThrough('fromProductIds', [$ids]);

        $this->toArrayInt()->shouldBeArray();
        $this->toArrayInt()->shouldBeLike($idsExpected);
    }

    public function it_finds_a_product_id()
    {
        $searchValue = new ProductId(12);
        $ids = [new ProductId(68), $searchValue, new ProductId(167)];
        $this->beConstructedThrough('fromProductIds', [$ids]);

        $result = $this->findByInt(12);

        $result->shouldBeAnInstanceOf(ProductId::class);
        $result->shouldBeLike($searchValue);
    }

    public function it_does_not_find_a_product_id()
    {
        $searchValue = new ProductId(12);
        $ids = [new ProductId(68), $searchValue, new ProductId(167)];
        $this->beConstructedThrough('fromProductIds', [$ids]);

        $result = $this->findByInt(1);

        $result->shouldBeNull();
    }

    public function it_counts_product_id_element()
    {
        $ids = [1, 2, 3];
        $this->beConstructedThrough('fromInts', [$ids]);
        $this->shouldHaveCount(3);
    }
}
