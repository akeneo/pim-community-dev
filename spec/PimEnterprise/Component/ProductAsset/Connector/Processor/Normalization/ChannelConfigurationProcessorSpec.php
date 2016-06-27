<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\Processor\Normalization;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfigurationInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ChannelConfigurationProcessorSpec extends ObjectBehavior
{
    function let(NormalizerInterface $channelNormalizer)
    {
        $this->beConstructedWith($channelNormalizer);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_processes(
        $channelNormalizer,
        ChannelVariationsConfigurationInterface $channelConf
    ) {
        $channelNormalizer->normalize($channelConf)->willReturn(
            ['channel' => 'channel_code', 'configuration' => ['resize' => ['width' => 400, 'height' => 200]]]
        );

        $this->process($channelConf)->shouldReturn(
            ['channel' => 'channel_code', 'configuration' => ['resize' => ['width' => 400, 'height' => 200]]]
        );
    }
}
