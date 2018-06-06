<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_adds_the_attribute_id_to_the_normalized_attribute($normalizer, AttributeInterface $price)
    {
        $normalizer->normalize($price, 'json', [])->willReturn([]);
        $price->getProperty('is_read_only')->willReturn(true);

        $this->normalize($price, 'json', [])->shouldReturn(['is_read_only' => true,]);
    }
}
