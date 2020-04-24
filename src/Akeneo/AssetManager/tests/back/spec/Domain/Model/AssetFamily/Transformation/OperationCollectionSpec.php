<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResizeOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ScaleOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use PhpSpec\ObjectBehavior;

class OperationCollectionSpec extends ObjectBehavior
{
    function it_creates_an_operation_collection()
    {
        $operation = ThumbnailOperation::create(['width' => 100, 'height' => 80]);
        $this->beConstructedThrough('create', [[$operation]]);
        $this->getWrappedObject();
    }

    function it_throws_an_exception_when_no_item_is_provided()
    {
        $this->beConstructedThrough('create', [[]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
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

    function it_is_equal_to_another_operation_collection()
    {
        $thumbnail = ThumbnailOperation::create(['width' => 100, 'height' => 80]);
        $resize = ResizeOperation::create(['width' => 100, 'height' => 80]);
        $this->beConstructedThrough('create', [[$thumbnail, $resize]]);

        $otherThumbnail = ThumbnailOperation::create(['width' => 100, 'height' => 80]);
        $otherResize = ResizeOperation::create(['width' => 100, 'height' => 80]);
        $otherCollection = OperationCollection::create([$otherThumbnail, $otherResize]);

        $this->equals($otherCollection)->shouldReturn(true);
    }

    function it_is_not_equal_to_another_operation_collection()
    {
        $thumbnail = ThumbnailOperation::create(['width' => 100, 'height' => 80]);
        $resize = ResizeOperation::create(['width' => 100, 'height' => 80]);
        $this->beConstructedThrough('create', [[$thumbnail, $resize]]);

        $otherThumbnail = ThumbnailOperation::create(['width' => 100, 'height' => 80]);
        $otherResize = ResizeOperation::create(['width' => 100, 'height' => 80]);
        $scale = ScaleOperation::create(['width' => 100, 'height' => 80]);
        $otherCollection = OperationCollection::create([$otherThumbnail, $otherResize, $scale]);
        $this->equals($otherCollection)->shouldReturn(false);

        $otherThumbnail = ThumbnailOperation::create(['width' => 100, 'height' => 80]);
        $otherResize = ResizeOperation::create(['width' => 100, 'height' => 60]);
        $otherCollection = OperationCollection::create([$otherThumbnail, $otherResize]);
        $this->equals($otherCollection)->shouldReturn(false);
    }

    function it_can_tell_if_it_contains_the_provided_operation()
    {
        $thumbnail = ThumbnailOperation::create(['width' => 100, 'height' => 80]);
        $resize = ResizeOperation::create(['width' => 100, 'height' => 80]);
        $this->beConstructedThrough('create', [[$thumbnail, $resize]]);

        $this->hasOperation('thumbnail')->shouldReturn(true);
        $this->hasOperation('unknown')->shouldReturn(false);
    }
}
