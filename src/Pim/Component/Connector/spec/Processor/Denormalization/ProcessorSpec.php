<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Item\ExecutionContext;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Factory\SimpleFactory;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * We test with a channel but it could be anything
 */
class ProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        SimpleFactory $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher

    ) {
        $this->beConstructedWith($repository, $factory, $updater, $validator, $objectDetacher);
    }

    function it_is_a_processor()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_updates_an_existing_channel(
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

        $updater
            ->update($channel, $values)
            ->shouldBeCalled();

        $validator
            ->validate($channel)
            ->willReturn($violationList);

        $this
            ->process($values)
            ->shouldReturn($channel);
    }

    function it_skips_a_channel_when_update_fails(
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

        $updater
            ->update($channel, $values)
            ->shouldBeCalled();

        $validator
            ->validate($channel)
            ->willReturn($violationList);

        $this
            ->process($values)
            ->shouldReturn($channel);

        $updater
            ->update($channel, $values)
            ->willThrow(new InvalidPropertyException('code', 'value', 'className', 'The code could not be blank.'));

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$values]
            );
    }

    function it_skips_a_channel_when_object_is_invalid(
        $repository,
        $updater,
        $validator,
        $objectDetacher,
        ChannelInterface $channel
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn($channel);

        $channel->getId()->willReturn(42);

        $values = $this->getValues();

        $updater
            ->update($channel, $values)
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
                [$values]
            );
    }

    function it_does_not_create_the_same_channel_twice_in_the_same_batch(
        $repository,
        $updater,
        $validator,
        $factory,
        StepExecution $stepExecution,
        ExecutionContext $executionContext,
        ConstraintViolationListInterface $violationList,
        ChannelInterface $channel
    ) {
        $this->setStepExecution($stepExecution);
        $repository->getIdentifierProperties()->willReturn(['code']);
        $stepExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get('processed_items_batch')->willReturn(null);

        $repository->findOneByIdentifier('mycode')->willReturn(null);
        $factory->create()->shouldBeCalledTimes(1)->willReturn($channel);

        $executionContext
            ->put('processed_items_batch', ['mycode' => $channel])
            ->shouldBeCalled()
            ->will(function() use ($executionContext, $channel) {
                $executionContext->get('processed_items_batch')->willReturn(['mycode' => $channel]);
            });

        $firstChannelValues = $this->getValues();

        $updater
            ->update($channel, $firstChannelValues)
            ->shouldBeCalled();

        $validator
            ->validate($channel)
            ->willReturn($violationList);

        $this
            ->process($firstChannelValues)
            ->shouldReturn($channel);

        $secondChannelValues = $this->getValues();
        $secondChannelValues['label'] = 'Another label';

        $updater
            ->update($channel, $secondChannelValues)
            ->shouldBeCalled();

        $validator
            ->validate($channel)
            ->willReturn($violationList);

        $this
            ->process($secondChannelValues)
            ->shouldReturn($channel);
    }

    protected function getValues()
    {
        return [
            'code'       => 'mycode',
            'label'      => 'Ecommerce',
            'locales'    => ['en_US', 'fr_FR'],
            'currencies' => ['EUR', 'USD'],
            'tree'       => 'master_catalog',
            'color'      => 'orange'
        ];
    }
}
