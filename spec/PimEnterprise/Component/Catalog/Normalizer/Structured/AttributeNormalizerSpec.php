<?php

namespace spec\PimEnterprise\Component\Catalog\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use PimEnterprise\Component\Catalog\Normalizer\Structured\AttributeNormalizer;
use Prophecy\Argument;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(AttributeNormalizer $attributeNormalizer) {
        $this->beConstructedWith($attributeNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Catalog\Normalizer\Structured\AttributeNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_normalizes_attribute($attributeNormalizer, AttributeInterface $attribute)
    {
        $attribute->getProperty('is_read_only')->willReturn(false);

        $attributeNormalizer->normalize($attribute, 'json', [])->willReturn([]);

        $this->normalize($attribute, 'json', [])->shouldReturn(['is_read_only' => 0]);
    }
}
