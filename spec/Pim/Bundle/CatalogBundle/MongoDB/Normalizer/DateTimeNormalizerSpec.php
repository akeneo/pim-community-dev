<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DateTimeNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_in_mongodb_json_of_media(\DateTime $time)
    {
        $this->supportsNormalization($time, 'mongodb_json')->shouldBe(true);
        $this->supportsNormalization($time, 'json')->shouldBe(false);
        $this->supportsNormalization($time, 'xml')->shouldBe(false);
    }

    function it_normalizes_price(\DateTime $time)
    {
        $time->getTimestamp()->willReturn('1331769600');

        $this->normalize($time, 'mongodb_json', [])->shouldReturn('1331769600');
    }
}
