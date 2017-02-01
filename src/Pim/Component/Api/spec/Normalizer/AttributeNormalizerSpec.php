<?php

namespace spec\Pim\Component\Api\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Normalizer\AttributeNormalizer;
use Pim\Component\Catalog\Model\AttributeInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeNormalizer::class);
    }

    function it_supports_a_attribute(AttributeInterface $attribute)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization($attribute, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($attribute, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_a_attribute($stdNormalizer, AttributeInterface $attribute)
    {
        $data = ['code' => 'my_attribute'];

        $stdNormalizer->normalize($attribute, 'external_api', [])->willReturn($data);

        $this->normalize($attribute, 'external_api', [])->shouldReturn($data);
    }
}
