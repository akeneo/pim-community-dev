<?php

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

class ProductIdFactorySpec extends ObjectBehavior
{
    public function it_creates_a_product_id()
    {
        $this->create('1234')->shouldBeLike(new ProductId(1234));
    }

    public function it_creates_a_collection_of_product_id()
    {
        $this->createCollection(['1234', '4321'])->shouldBeLike(new ProductIdCollection([1234, 4321]));
    }

    public function it_throws_exception_when_invalid_id()
    {

    }

    public function it_throws_exception_when_invalid_list_of_ids()
    {

    }

}
