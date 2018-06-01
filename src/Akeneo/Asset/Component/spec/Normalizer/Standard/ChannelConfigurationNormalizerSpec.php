<?php

namespace spec\Akeneo\Asset\Component\Normalizer\Standard;

use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Asset\Component\Model\ChannelVariationsConfigurationInterface;

class ChannelConfigurationNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Asset\Component\Normalizer\Standard\ChannelConfigurationNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
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
