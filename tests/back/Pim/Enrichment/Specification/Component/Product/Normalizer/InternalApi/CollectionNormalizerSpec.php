<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

class CollectionNormalizerSpec extends ObjectBehavior
{
    function it_supports_iterables()
    {
        $this->supportsNormalization(new ArrayCollection([]), 'internal_api')->shouldReturn(true);
        $this->supportsNormalization([], 'internal_api')->shouldReturn(true);
    }
}
