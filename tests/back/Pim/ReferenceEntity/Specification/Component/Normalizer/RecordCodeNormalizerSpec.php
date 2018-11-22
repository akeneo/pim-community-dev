<?php

namespace Specification\Akeneo\Pim\ReferenceEntity\Component\Normalizer;

use Akeneo\Pim\ReferenceEntity\Component\Normalizer\RecordCodeNormalizer;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RecordCodeNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NormalizerInterface::class);
        $this->shouldHaveType(RecordCodeNormalizer::class);
    }

    function it_normalizes_a_record_code(RecordCode $starckCode)
    {
        $starckCode->normalize()->willReturn('starck');

        $this->normalize($starckCode, 'standard')->shouldReturn('starck');
    }

    function it_normalizes_a_record_code_with_the_field_name(RecordCode $starckCode)
    {
        $starckCode->normalize()->willReturn('starck');

        $this->normalize($starckCode, 'standard', ['field_name' => 'designer'])->shouldReturn('starck');
    }

    function it_supports_a_record_code(RecordCode $starck)
    {
        $this->supportsNormalization($starck, 'standard')->shouldReturn(true);
        $this->supportsNormalization($starck, 'storage')->shouldReturn(true);
        $this->supportsNormalization($starck, 'flat')->shouldReturn(true);
        $this->supportsNormalization($starck, 'structure')->shouldReturn(false);
        $this->supportsNormalization(false, 'standard')->shouldReturn(false);
    }
}
