<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use PhpSpec\ObjectBehavior;

class TransformationCollectionSpec extends ObjectBehavior
{
    function it_creates_a_transformation_collection(Transformation $transformation)
    {
        $this->beConstructedThrough('create', [[$transformation]]);
        $this->shouldHaveType(TransformationCollection::class);
    }

    function it_throws_an_exception_when_a_collection_item_is_not_a_transformation(
        Transformation $transformation,
        Transformation $otherTransformation
    ) {
        $this->beConstructedThrough('create',[
            [
                $transformation,
                new \stdClass(),
                $otherTransformation,
            ],
        ]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    // TODO ATR-28
    function it_cannot_have_two_transformations_with_the_same_target()
    {

    }
}
