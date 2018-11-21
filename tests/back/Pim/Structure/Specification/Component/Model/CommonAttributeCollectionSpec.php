<?php

namespace Specification\Akeneo\Pim\Structure\Component\Model;

use Akeneo\Pim\Structure\Component\Model\CommonAttributeCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;

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
