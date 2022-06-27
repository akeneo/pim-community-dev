<?php

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

class ProductUuidFactorySpec extends ObjectBehavior
{
    public function it_creates_a_product_uuid()
    {
        $this->create(Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed'))->shouldBeLike(ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')));
    }

    public function it_creates_a_collection_of_product_uuids()
    {
        $collectionBehavior = $this->createCollection([
            'df470d52-7723-4890-85a0-e79be625e2ed',
            '6d125b99-d971-41d9-a264-b020cd486aee'
        ]);

        $collection = $collectionBehavior->getWrappedObject();
        Assert::allIsInstanceOf($collection, ProductUuid:: class);
        Assert::same((string) $collection->toArray()[0], 'df470d52-7723-4890-85a0-e79be625e2ed');
        Assert::same((string) $collection->toArray()[1], '6d125b99-d971-41d9-a264-b020cd486aee');
    }

    public function it_throws_exception_when_invalid_uuid()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('create', ['6d125b99']);
    }

    public function it_throws_exception_when_invalid_list_of_uuids()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('createCollection', [['6d125b99']]);
    }
}
