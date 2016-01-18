<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GroupNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    function it_supports_groups(GroupInterface $tshirt)
    {
        $this->supportsNormalization($tshirt, 'internal_api')->shouldReturn(true);
    }

    function it_normalizes_groups($normalizer, GroupInterface $tshirt)
    {
        $normalizer->normalize($tshirt, 'json', [])->willReturn([
            'normalized_property'          => 'the_first_one',
            'an_other_normalized_property' => 'the_second_one',
        ]);

        $tshirt->getId()->willReturn(12);

        $this->normalize($tshirt, 'internal_api')->shouldReturn([
            'normalized_property'          => 'the_first_one',
            'an_other_normalized_property' => 'the_second_one',
            'meta'                         => ['id' => 12]
        ]);
    }
}
