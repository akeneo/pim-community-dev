<?php

namespace spec\Akeneo\Asset\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Asset\Component\Factory\ChannelConfigurationFactory;
use Akeneo\Asset\Component\Model\ChannelVariationsConfigurationInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChannelConfigurationProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $configurationRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        ChannelConfigurationFactory $configurationFactory,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $configurationRepository,
            $channelRepository,
            $configurationFactory,
            $validator
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_processor()
    {
        $this->shouldImplement('Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_updates_an_existing_channel_configuration(
        $channelRepository,
        $configurationRepository,
        $validator,
        ChannelInterface $channel,
        ChannelVariationsConfigurationInterface $configuration,
        ConstraintViolationListInterface $violationList
    ) {
        $convertedItem = [
            'channel'       => 'ecommerce',
            'configuration' => ['scale' => ['ratio' => 0.5]]
        ];

        $channelRepository->getIdentifierProperties()->willReturn(['code']);
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $configurationRepository->getIdentifierProperties()->willReturn(['channel']);
        $configurationRepository->findOneByIdentifier($channel)->willReturn($configuration);

        $configuration
            ->setConfiguration($convertedItem['configuration'])
            ->shouldBeCalled();

        $validator
            ->validate($configuration)
            ->willReturn($violationList);

        $this
            ->process($convertedItem)
            ->shouldReturn($configuration);
    }

    function it_creates_a_channel_configuration(
        $channelRepository,
        $configurationRepository,
        $configurationFactory,
        $validator,
        ChannelInterface $channel,
        ChannelVariationsConfigurationInterface $configuration,
        ConstraintViolationListInterface $violationList
    ) {
        $convertedItem = [
            'channel'       => 'ecommerce',
            'configuration' => ['scale' => ['ratio' => 0.5]]
        ];

        $channelRepository->getIdentifierProperties()->willReturn(['code']);
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $configurationRepository->getIdentifierProperties()->willReturn(['channel']);
        $configurationRepository->findOneByIdentifier($channel)->willReturn(null);
        $configurationFactory->createChannelConfiguration()->willReturn($configuration);

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
            ->process($convertedItem)
            ->shouldReturn($configuration);
    }

    function it_skips_a_channel_configuration_when_object_is_invalid(
        $channelRepository,
        $configurationRepository,
        $validator,
        ChannelInterface $channel,
        ChannelVariationsConfigurationInterface $configuration
    ) {
        $convertedItem = [
            'channel'       => 'ecommerce',
            'configuration' => ['scale' => ['ratio' => 0.5]]
        ];

        $channelRepository->getIdentifierProperties()->willReturn(['code']);
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $configurationRepository->getIdentifierProperties()->willReturn(['channel']);
        $configurationRepository->findOneByIdentifier($channel)->willReturn($configuration);

        $configuration
            ->setConfiguration($convertedItem['configuration'])
            ->shouldBeCalled();

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator
            ->validate($configuration)
            ->willReturn($violations);


        $this
            ->shouldThrow('Akeneo\Tool\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$convertedItem]
            );
    }
}
