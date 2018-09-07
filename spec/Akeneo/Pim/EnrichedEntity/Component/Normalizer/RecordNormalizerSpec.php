<?php

namespace spec\Akeneo\Pim\EnrichedEntity\Component\Normalizer;

use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\Pim\EnrichedEntity\Component\Normalizer\RecordNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;

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
