<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\Normalizer\MongoDB;

use PhpSpec\ObjectBehavior;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

class ReferenceDataNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_of_reference_data_in_mongodb_document(ReferenceDataInterface $refData)
    {
        $this->supportsNormalization($refData, 'mongodb_document')->shouldReturn(true);
    }

    function it_does_not_support_normalization_of_other_entities(\stdClass $object)
    {
        $this->supportsNormalization($object, 'mongodb_document')->shouldReturn(false);
    }

    function it_does_not_support_normalization_of_reference_data_into_other_format(ReferenceDataInterface $refData)
    {
        $this->supportsNormalization($refData, 'json')->shouldReturn(false);
    }

    function it_normalizes_a_reference_data_into_mongodb_document(ReferenceDataInterface $refData) {
        $refData->getId()->willReturn('ref_id');
        $this->normalize($refData, 'mongodb_document')->shouldReturn('ref_id');
    }
}
