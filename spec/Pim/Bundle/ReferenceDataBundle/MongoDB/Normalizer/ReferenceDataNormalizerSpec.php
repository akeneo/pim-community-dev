<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\MongoDB\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

class ReferenceDataNormalizerSpec extends ObjectBehavior
{
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

    function it_normalizes(ReferenceDataInterface $referenceData)
    {
        $referenceData->getId()->willReturn('my-id');
        $referenceData->getCode()->willReturn('my-reference-data');
        $this->normalize($referenceData, 'mongodb_json')->shouldReturn(['id' => 'my-id', 'code' => 'my-reference-data']);
    }
}
