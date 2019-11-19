<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use PhpSpec\ObjectBehavior;

class OperationCollectionSpec extends ObjectBehavior
{
    function it_creates_an_operation_collection(Operation $operation)
    {
        $this->beConstructedThrough('create', [[$operation]]);
        $this->getWrappedObject();
    }

    function it_throws_an_exception_when_a_collection_item_is_not_an_operation(Operation $thumbnail, Operation $resize)
    {
        $this->beConstructedThrough('create', [[$thumbnail, new \stdClass(), $resize]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
