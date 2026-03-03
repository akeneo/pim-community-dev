<?php

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

class ProductModelIdFactorySpec extends ObjectBehavior
{
    public function it_creates_a_product_model_id()
    {
        $this->create('1234')->shouldBeLike(new ProductModelId(1234));
    }

    public function it_creates_a_collection_of_product_model_id()
    {
        $collectionBehavior = $this->createCollection(['1234', '4321']);

        $collection = $collectionBehavior->getWrappedObject();
        Assert::allIsInstanceOf($collection, ProductModelId:: class);
        Assert::same((string) $collection->toArray()[0], '1234');
        Assert::same((string) $collection->toArray()[1], '4321');
    }

    public function it_throws_exception_when_invalid_id()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('create', ['abcd']);
        $this->shouldThrow(\InvalidArgumentException::class)->during('create', ['-1234']);
    }

    public function it_throws_exception_when_invalid_list_of_ids()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('createCollection', [['abcd']]);
        $this->shouldThrow(\InvalidArgumentException::class)->during('createCollection', [['-1234']]);
    }
}
