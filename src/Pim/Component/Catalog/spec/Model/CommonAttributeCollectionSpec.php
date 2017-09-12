<?php

namespace spec\Pim\Component\Catalog\Model;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\CommonAttributeCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CommonAttributeCollectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CommonAttributeCollection::class);
    }

    function it_is_a_collection()
    {
        $this->shouldImplement(Collection::class);
    }

    function it_creates_a_collection_from_another(Collection $collection)
    {
        $this->beConstructedThrough('fromCollection', [$collection]);
    }
}
