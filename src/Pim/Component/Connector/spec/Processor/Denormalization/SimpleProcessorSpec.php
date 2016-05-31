<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Factory\SimpleFactory;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * We test with a channel but it could be anything
 */
class SimpleProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        ArrayConverterInterface $converter,
        SimpleFactory $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($repository, $converter, $factory, $updater, $validator, $objectDetacher);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_updates_an_existing_channel(
        $converter,
        $repository,
        $updater,
        $validator,
        ChannelInterface $channel,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('mycode')->willReturn($channel);

        $channel->getId()->willReturn(42);

        $values = $this->getValues();

        $converter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $updater
            ->update($channel, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($channel)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn($channel);
    }

    function it_skips_a_channel_when_update_fails(
        $converter,
        $repository,
        $updater,
        $validator,

        ChannelInterface $channel,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn($channel);

        $channel->getId()->willReturn(42);

        $values = $this->getValues();

        $converter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $updater
            ->update($channel, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($channel)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn($channel);

        $updater
            ->update($channel, $values['converted_values'])
            ->willThrow(new \InvalidArgumentException());

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    function it_skips_a_channel_when_object_is_invalid(
        $converter,
        $repository,
        $updater,
        $validator,
        $objectDetacher,
        ChannelInterface $channel,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn($channel);

        $channel->getId()->willReturn(42);

        $values = $this->getValues();

        $converter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $updater
            ->update($channel, $values['converted_values'])
            ->shouldBeCalled();

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($channel)
            ->willReturn($violations);

        $objectDetacher->detach($channel)->shouldBeCalled();
        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    protected function getValues()
    {
        return [
            'original_values' => [
               'code'         => 'mycode',
               'label'        => 'Ecommerce',
               'locales'      => 'en_US,fr_FR',
               'currencies'   => 'EUR,USD',
               'tree'         => 'master_catalog',
               'color'        => 'orange'
            ],
            'converted_values' => [
              'code'   => 'mycode',
              'label'  => 'Ecommerce',
              'locales'    => ['en_US', 'fr_FR'],
              'currencies' => ['EUR', 'USD'],
              'tree'       => 'master_catalog',
              'color'      => 'orange'
            ]
        ];
    }
}
