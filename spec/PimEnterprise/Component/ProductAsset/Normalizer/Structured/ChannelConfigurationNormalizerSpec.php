<?php

namespace spec\PimEnterprise\Component\ProductAsset\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfigurationInterface;

class ChannelConfigurationNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_should_normalize(ChannelVariationsConfigurationInterface $channelConf, ChannelInterface $channel)
    {
        $normalizedValues = [
            'channel'       => 'channel_code',
            'configuration' => [
                'resize' => [
                    'width'  => 400,
                    'height' => 200
                ]
            ],
        ];

        $channel->getCode()->willReturn('channel_code');
        $channelConf->getChannel()->willReturn($channel);
        $channelConf->getConfiguration()->willReturn(['resize' => ['width' => 400, 'height' => 200]]);

        $this->normalize($channelConf)->shouldReturn($normalizedValues);
    }
}
