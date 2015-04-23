<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\MongoDB\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Component\ReferenceData\LabelRenderer;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Prophecy\Argument;

class ReferenceDataNormalizerSpec extends ObjectBehavior
{
    function let(LabelRenderer $renderer)
    {
        $this->beConstructedWith($renderer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ReferenceDataBundle\MongoDB\Normalizer\ReferenceDataNormalizer');
    }

    function it_supports_normalization(ReferenceDataInterface $referenceData, AttributeOptionInterface $option)
    {
        $this->supportsNormalization($referenceData, 'mongodb_json')->shouldReturn(true);
        $this->supportsNormalization($referenceData, 'wrong_format')->shouldReturn(false);
        $this->supportsNormalization($option, 'mongodb_json')->shouldReturn(false);
        $this->supportsNormalization($option, 'wrong_format')->shouldReturn(false);
    }

    function it_normalizes_with_a_reference_data_with_a_label($renderer, ReferenceDataInterface $referenceData)
    {
        $renderer->getLabelProperty($referenceData)->willReturn('label');
        $renderer->render($referenceData, false)->willReturn('My Beautiful Reference Data');

        $referenceData->getId()->willReturn('my-id');
        $referenceData->getCode()->willReturn('my-reference-data');

        $this->normalize($referenceData, 'mongodb_json')->shouldReturn(
            ['id' => 'my-id', 'code' => 'my-reference-data', 'label' => 'My Beautiful Reference Data']
        );
    }

    function it_normalizes_with_a_reference_data_without_a_label($renderer, ReferenceDataInterface $referenceData)
    {
        $renderer->getLabelProperty(Argument::any())->shouldNotBeCalled();
        $renderer->render($referenceData, false)->willReturn(null);

        $referenceData->getId()->willReturn('my-id');
        $referenceData->getCode()->willReturn('my-reference-data');

        $this->normalize($referenceData, 'mongodb_json')->shouldReturn(
            ['id' => 'my-id', 'code' => 'my-reference-data']
        );
    }
}
