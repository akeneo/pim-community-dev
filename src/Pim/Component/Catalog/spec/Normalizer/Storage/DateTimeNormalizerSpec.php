<?php

namespace spec\Pim\Component\Catalog\Normalizer\Storage;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Normalizer\Storage\DateTimeNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateTimeNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DateTimeNormalizer::class);
    }

    function it_support_dates(\Datetime $date)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'storage')->shouldReturn(false);
        $this->supportsNormalization($date, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($date, 'storage')->shouldReturn(true);
    }

    function it_normalizes_product_assocations($stdNormalizer, \Datetime $date)
    {
        $stdNormalizer->normalize($date, 'storage', ['context'])->willReturn('date');

        $this->normalize($date, 'storage', ['context'])->shouldReturn('date');
    }
}
