<?php

namespace spec\PimEnterprise\Component\Catalog\Normalizer\Standard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use PimEnterprise\Component\Catalog\Normalizer\Standard\AttributeNormalizer;
use Prophecy\Argument;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(AttributeNormalizer $attributeNormalizer)
    {
        $this->beConstructedWith($attributeNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Catalog\Normalizer\Standard\AttributeNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization(AttributeInterface $attribute)
    {
        $this->supportsNormalization($attribute, 'standard')->shouldBe(true);
        $this->supportsNormalization($attribute, 'json')->shouldBe(false);
        $this->supportsNormalization($attribute, 'xml')->shouldBe(false);
    }

    function it_normalizes_attribute($attributeNormalizer, AttributeInterface $attribute)
    {
        $attribute->getProperty('is_read_only')->willReturn(false);

        $attributeNormalizer->normalize($attribute, 'standard', [])->willReturn([]);

        $this->normalize($attribute, 'standard', [])->shouldReturn(['is_read_only' => false]);
    }
}
