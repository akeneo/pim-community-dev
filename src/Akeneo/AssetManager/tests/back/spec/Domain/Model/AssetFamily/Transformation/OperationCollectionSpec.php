<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResizeOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use PhpSpec\ObjectBehavior;

class OperationCollectionSpec extends ObjectBehavior
{
    function it_creates_an_operation_collection()
    {
        $operation = ThumbnailOperation::create(['width' => 100, 'height' => 80]);
        $this->beConstructedThrough('create', [[$operation]]);
        $this->getWrappedObject();
    }

    function it_throws_an_exception_when_a_collection_item_is_not_an_operation()
    {
        $thumbnail = ThumbnailOperation::create(['width' => 100, 'height' => 80]);
        $resize = ResizeOperation::create(['width' => 100, 'height' => 80]);
        $this->beConstructedThrough('create', [[$thumbnail, new \stdClass(), $resize]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_normalizes_an_operation_collection()
    {
        $thumbnail = ThumbnailOperation::create(['width' => 100, 'height' => 80]);
        $resize = ResizeOperation::create(['width' => 100, 'height' => 80]);
        $this->beConstructedThrough('create', [[$thumbnail, $resize]]);
        $this->normalize()->shouldReturn([$thumbnail->normalize(), $resize->normalize()]);
    }
}
