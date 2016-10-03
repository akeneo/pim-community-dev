<?php

namespace spec\PimEnterprise\Component\ProductAsset\Normalizer\Standard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfigurationInterface;

class ChannelConfigurationNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Normalizer\Standard\ChannelConfigurationNormalizer');
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
