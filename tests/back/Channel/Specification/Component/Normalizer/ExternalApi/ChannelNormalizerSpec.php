<?php

namespace Specification\Akeneo\Channel\Component\Normalizer\ExternalApi;

use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Normalizer\ExternalApi\ChannelNormalizer;
use Akeneo\Channel\Component\Model\ChannelInterface;
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
        $data = ['code' => 'my_channel', 'labels' => [], 'conversion_units' => []];

        $stdNormalizer->normalize($channel, 'standard', [])->willReturn($data);

        $normalizedChannel = $this->normalize($channel, 'external_api', []);
        $normalizedChannel->shouldHaveLabels($data);
        $normalizedChannel->shouldHaveConversionUnits($data);
    }

    public function getMatchers(): array
    {
        return [
            'haveLabels' => function ($subject) {
                return is_object($subject['labels']);
            },
            'haveConversionUnits' => function($subject) {
                return is_object($subject['conversion_units']);
            }
        ];
    }
}
