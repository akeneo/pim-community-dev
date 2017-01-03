<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\SerializerInterface;

class CollectionNormalizerSpec extends ObjectBehavior
{
    function it_supports_iterables()
    {
        $this->supportsNormalization(new ArrayCollection([]), 'internal_api')->shouldReturn(true);
        $this->supportsNormalization([], 'internal_api')->shouldReturn(true);
    }
}
