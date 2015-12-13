<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use PimEnterprise\Component\ProductAsset\Factory\ChannelConfigurationFactory;
use PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfigurationInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChannelConfigurationProcessorSpec extends ObjectBehavior
{
    function let(
        StandardArrayConverterInterface $configurationConverter,
        IdentifiableObjectRepositoryInterface $configurationRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        ChannelConfigurationFactory $configurationFactory,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $configurationConverter,
            $configurationRepository,
            $channelRepository,
            $configurationFactory,
            $validator
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_has_no_extra_configuration()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_updates_an_existing_channel_configuration(
        $configurationConverter,
        $channelRepository,
        $configurationRepository,
        $validator,
        ChannelInterface $channel,
        ChannelVariationsConfigurationInterface $configuration,
        ConstraintViolationListInterface $violationList
    ) {
        $item = [
            'channel'       => 'ecommerce',
            'configuration' => '{"scale":{"ratio":0.5}}'
        ];
        $convertedItem = [
            'channel'       => 'ecommerce',
            'configuration' => ['scale' => ['ratio' => 0.5]]
        ];

        $channelRepository->getIdentifierProperties()->willReturn(['code']);
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $configurationRepository->getIdentifierProperties()->willReturn(['channel']);
        $configurationRepository->findOneByIdentifier($channel)->willReturn($configuration);

        $configurationConverter
            ->convert($item)
            ->willReturn($convertedItem);

        $configuration
            ->setConfiguration($convertedItem['configuration'])
            ->shouldBeCalled();

        $validator
            ->validate($configuration)
            ->willReturn($violationList);

        $this
            ->process($item)
            ->shouldReturn($configuration);
    }

    function it_creates_a_channel_configuration(
        $configurationConverter,
        $channelRepository,
        $configurationRepository,
        $configurationFactory,
        $validator,
        ChannelInterface $channel,
        ChannelVariationsConfigurationInterface $configuration,
        ConstraintViolationListInterface $violationList
    ) {
        $item = [
            'channel'       => 'ecommerce',
            'configuration' => '{"scale":{"ratio":0.5}}'
        ];
        $convertedItem = [
            'channel'       => 'ecommerce',
            'configuration' => ['scale' => ['ratio' => 0.5]]
        ];

        $channelRepository->getIdentifierProperties()->willReturn(['code']);
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $configurationRepository->getIdentifierProperties()->willReturn(['channel']);
        $configurationRepository->findOneByIdentifier($channel)->willReturn(null);
        $configurationFactory->createChannelConfiguration()->willReturn($configuration);

        $configurationConverter
            ->convert($item)
            ->willReturn($convertedItem);

        $configuration
            ->setChannel($channel)
            ->shouldBeCalled();

        $configuration
            ->setConfiguration($convertedItem['configuration'])
            ->shouldBeCalled();

        $validator
            ->validate($configuration)
            ->willReturn($violationList);

        $this
            ->process($item)
            ->shouldReturn($configuration);
    }

    function it_skips_a_channel_configuration_when_object_is_invalid(
        $configurationConverter,
        $channelRepository,
        $configurationRepository,
        $validator,
        ChannelInterface $channel,
        ChannelVariationsConfigurationInterface $configuration
    ) {
        $item = [
            'channel'       => 'ecommerce',
            'configuration' => '{"scale":{"ratio":0.5}}'
        ];
        $convertedItem = [
            'channel'       => 'ecommerce',
            'configuration' => ['scale' => ['ratio' => 0.5]]
        ];

        $channelRepository->getIdentifierProperties()->willReturn(['code']);
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $configurationRepository->getIdentifierProperties()->willReturn(['channel']);
        $configurationRepository->findOneByIdentifier($channel)->willReturn($configuration);

        $configurationConverter
            ->convert($item)
            ->willReturn($convertedItem);

        $configuration
            ->setConfiguration($convertedItem['configuration'])
            ->shouldBeCalled();

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator
            ->validate($configuration)
            ->willReturn($violations);


        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$item]
            );
    }
}
