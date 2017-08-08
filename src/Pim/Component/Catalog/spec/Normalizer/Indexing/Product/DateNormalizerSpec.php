<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Normalizer\Indexing\ProductValue\DateNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $standardNormalizer)
    {
        $this->beConstructedWith($standardNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DateNormalizer::class);
    }

    function it_support_dates(\Datetime $date)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization($date, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($date, 'indexing')->shouldReturn(true);
    }

    function it_normalizes_product_assocations($standardNormalizer, \Datetime $date)
    {
        $standardNormalizer->normalize($date, 'indexing', ['context'])->willReturn('date');

        $this->normalize($date, 'indexing', ['context'])->shouldReturn('date');
    }
}
