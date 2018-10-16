<?php

namespace Specification\Akeneo\Asset\Component\Normalizer\Standard;

use Akeneo\Asset\Component\Normalizer\Standard\ChannelConfigurationNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Asset\Component\Model\ChannelVariationsConfigurationInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ChannelConfigurationNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ChannelConfigurationNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_standard_normalization(ChannelVariationsConfigurationInterface $channelConf)
    {
        $this->supportsNormalization($channelConf, 'standard')->shouldBe(true);
        $this->supportsNormalization($channelConf, 'json')->shouldBe(false);
        $this->supportsNormalization($channelConf, 'xml')->shouldBe(false);
    }

    function it_normalizes_channel_variation_configuration(
        ChannelVariationsConfigurationInterface $channelConf,
        ChannelInterface $channel
    ) {
        $channel->getCode()->willReturn('channel_code');
        $channelConf->getChannel()->willReturn($channel);
        $channelConf->getConfiguration()->willReturn(['resize' => ['width' => 400, 'height' => 200]]);

        $this->normalize($channelConf)->shouldReturn(
            [
                'channel' => 'channel_code',
                'configuration' => [
                    'resize' => [
                        'width'  => 400,
                        'height' => 200,
                    ],
                ],
            ]
        );
    }
}
