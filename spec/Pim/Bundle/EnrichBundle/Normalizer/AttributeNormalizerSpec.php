<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerSpec extends ObjectBehavior
{
    public function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    public function it_adds_the_attribute_id_to_the_normalized_attribute($normalizer, AttributeInterface $price)
    {
        $normalizer->normalize($price, 'json', [])->willReturn(['code' => 'price']);
        $price->getId()->willReturn(12);

        $this->normalize($price, 'internal_api', [])->shouldReturn(['code' => 'price', 'id' => 12]);
    }

    public function it_supports_attributes_and_internal_api(AttributeInterface $price)
    {
        $this->supportsNormalization($price, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($price, 'json')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'internal_api')->shouldReturn(false);
    }
}
