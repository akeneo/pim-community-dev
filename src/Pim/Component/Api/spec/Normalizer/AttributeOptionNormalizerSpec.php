<?php

namespace spec\Pim\Component\Api\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Normalizer\AttributeOptionNormalizer;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeOptionNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeOptionNormalizer::class);
    }

    function it_supports_a_attribute_option(AttributeOptionInterface $attributeOption)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization($attributeOption, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($attributeOption, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_a_attribute_option($stdNormalizer, AttributeOptionInterface $attributeOption)
    {
        $data = ['code' => 'my_attribute_option'];

        $stdNormalizer->normalize($attributeOption, 'external_api', [])->willReturn($data);

        $this->normalize($attributeOption, 'external_api', [])->shouldReturn($data);
    }
}
