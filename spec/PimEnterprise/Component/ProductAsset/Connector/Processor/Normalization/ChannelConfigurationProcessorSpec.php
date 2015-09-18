<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\Processor\Normalization;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfigurationInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ChannelConfigurationProcessorSpec extends ObjectBehavior
{
    function let(
        SerializerInterface $serializer,
        LocaleManager $localeManager,
        NormalizerInterface $channelNormalizer
    ) {
        $this->beConstructedWith(
            $serializer,
            $localeManager,
            $channelNormalizer
        );
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_has_no_extra_configuration()
    {
        $this->getConfigurationFields()->shouldReturn([]);
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
