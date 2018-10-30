<?php

namespace Specification\Akeneo\Pim\ReferenceEntity\Component\Normalizer;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\Pim\ReferenceEntity\Component\Normalizer\RecordNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;

class RecordNormalizerSpec extends ObjectBehavior {
    function it_is_initializable()
    {
        $this->shouldHaveType(NormalizerInterface::class);
        $this->shouldHaveType(RecordNormalizer::class);
    }

    function it_normalize_a_record(Record $starck, RecordCode $starckCode)
    {
        $starck->getCode()->willReturn($starckCode);
        $starckCode->__toString()->willReturn('starck');

        $this->normalize($starck, 'standard')->shouldReturn('starck');
    }

    function it_supports_a_record(Record $starck)
    {
        $this->supportsNormalization($starck, 'standard')->shouldReturn(true);
        $this->supportsNormalization($starck, 'storage')->shouldReturn(true);
        $this->supportsNormalization($starck, 'flat')->shouldReturn(true);
        $this->supportsNormalization($starck, 'structure')->shouldReturn(false);
        $this->supportsNormalization(false, 'standard')->shouldReturn(false);
    }
}
