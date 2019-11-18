<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TransformationCollectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TransformationCollection::class);
    }
}
