<?php

namespace spec\Pim\Component\Api\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Normalizer\ChannelNormalizer;
use Pim\Component\Catalog\Model\ChannelInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ChannelNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChannelNormalizer::class);
    }

    function it_supports_a_channel(ChannelInterface $channel)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization($channel, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($channel, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_a_channel($stdNormalizer, ChannelInterface $channel)
    {
        $data = ['code' => 'my_channel'];

        $stdNormalizer->normalize($channel, 'external_api', [])->willReturn($data);

        $this->normalize($channel, 'external_api', [])->shouldReturn($data);
    }
}
