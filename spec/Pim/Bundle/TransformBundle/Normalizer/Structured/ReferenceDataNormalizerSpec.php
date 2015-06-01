<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

class ReferenceDataNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_xml_and_json_normalization_of_reference_data(ReferenceDataInterface $starship)
    {
        $this->supportsNormalization($starship, 'xml')->shouldBe(true);
        $this->supportsNormalization($starship, 'json')->shouldBe(true);
        $this->supportsNormalization($starship, 'csv')->shouldBe(false);
    }

    function it_does_not_support_json_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'json')->shouldBe(false);
    }

    function it_normalizes_a_reference_data_model(ReferenceDataInterface $starship)
    {
        $starship->getCode()->willReturn('battlecruiser');

        $this->normalize($starship, 'xml', [])->shouldReturn(['code' => 'battlecruiser']);
        $this->normalize($starship, 'json', [])->shouldReturn(['code' => 'battlecruiser']);
    }
}
