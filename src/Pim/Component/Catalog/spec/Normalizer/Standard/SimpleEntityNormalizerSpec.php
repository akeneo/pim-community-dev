<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Prophecy\Argument;

class SimpleEntityNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\SimpleEntityNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_format_only(
        AttributeOptionInterface $attributeOption,
        ReferenceDataInterface $referenceData
    ) {
        $notSupportedObject = [];

        $this->supportsNormalization($attributeOption, 'standard')->shouldReturn(true);
        $this->supportsNormalization($referenceData, 'standard')->shouldReturn(true);
        $this->supportsNormalization($attributeOption, 'other_format')->shouldReturn(false);
        $this->supportsNormalization($notSupportedObject, 'standard')->shouldReturn(false);
        $this->supportsNormalization($notSupportedObject, 'standard')->shouldReturn(false);
        $this->supportsNormalization($notSupportedObject, 'other_format')->shouldReturn(false);
    }

    function it_normalizes_simple_entities(AttributeOptionInterface $attributeOption)
    {
        $attributeOption->getCode()->willReturn('entity_code');
        $this->normalize($attributeOption)->shouldReturn('entity_code');
    }
}
