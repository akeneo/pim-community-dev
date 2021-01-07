<?php

namespace spec\Akeneo\Tool\Component\Batch\Step;

use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ItemStepSpec extends ObjectBehavior
{
    function let(
        EventDispatcherInterface $dispatcher,
        DoctrineJobRepository $repository,
        ItemReaderInterface $reader,
        ItemProcessorInterface $processor,
        ItemWriterInterface $writer,
        JobStopper $jobStopper
    ) {
        $this->beConstructedWith(
            'myname',
            $dispatcher,
            $repository,
            $reader,
            $processor,
            $writer,
            3,
            $jobStopper
        );
    }

    function it_executes_with_success(
        $reader,
        $processor,
        $writer,
        $dispatcher,
        $repository,
        StepExecution $execution,
        BatchStatus $status,
        ExitStatus $exitStatus,
        JobStopper $jobStopper
    ) {
        $execution->getStatus()->willReturn($status);
        $status->getValue()->willReturn(BatchStatus::STARTING);

        $dispatcher->dispatch(Argument::any(), EventInterface::BEFORE_STEP_EXECUTION)->shouldBeCalled();
        $execution->setStartTime(Argument::any())->shouldBeCalled();
        $execution->setStatus(Argument::any())->shouldBeCalled();

        // first batch
        $reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $processor->process('r1')->shouldBeCalled()->willReturn('p1');
        $processor->process('r2')->shouldBeCalled()->willReturn('p2');
        $processor->process('r3')->shouldBeCalled()->willReturn('p3');
        $writer->write(['p1', 'p2', 'p3'])->shouldBeCalled();
        $execution->incrementProcessedItems(3)->shouldBeCalledOnce();

        $dispatcher->dispatch(Argument::any(), EventInterface::ITEM_STEP_AFTER_BATCH)->shouldBeCalled();
        $jobStopper->isStopping($execution)->willReturn(false);

        // second batch
        $processor->process('r4')->shouldBeCalled()->willReturn('p4');
        $processor->process(null)->shouldNotBeCalled();
        $writer->write(['p4'])->shouldBeCalled();
        $execution->incrementProcessedItems(1)->shouldBeCalledOnce();

        $dispatcher->dispatch(Argument::any(), EventInterface::ITEM_STEP_AFTER_BATCH)->shouldBeCalled();
        $jobStopper->isStopping($execution)->willReturn(false);

        $execution->getExitStatus()->willReturn($exitStatus);
        $exitStatus->getExitCode()->willReturn(ExitStatus::COMPLETED);
        $repository->updateStepExecution($execution)->shouldBeCalledTimes(5);
        $execution->isTerminateOnly()->willReturn(false);

        $execution->upgradeStatus(Argument::any())->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::STEP_EXECUTION_SUCCEEDED)->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::STEP_EXECUTION_COMPLETED)->shouldBeCalled();
        $execution->setEndTime(Argument::any())->shouldBeCalled();
        $execution->setExitStatus(Argument::any())->shouldBeCalled();

        $this->execute($execution);
    }

    function it_executes_with_an_invalid_item_during_processing(
        $reader,
        $processor,
        $writer,
        $dispatcher,
        $repository,
        StepExecution $execution,
        BatchStatus $status,
        ExitStatus $exitStatus,
        JobStopper $jobStopper
    ) {
        $execution->getStatus()->willReturn($status);
        $status->getValue()->willReturn(BatchStatus::STARTING);

        $dispatcher->dispatch(Argument::any(), EventInterface::BEFORE_STEP_EXECUTION)->shouldBeCalled();
        $execution->setStartTime(Argument::any())->shouldBeCalled();
        $execution->setStatus(Argument::any())->shouldBeCalled();

        // first batch
        $reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $processor->process('r1')->shouldBeCalled()->willReturn('p1');
        $processor->process('r2')->shouldBeCalled()->willReturn('p2');
        $processor->process('r3')->shouldBeCalled()->willReturn('p3');
        $writer->write(['p1', 'p2', 'p3'])->shouldBeCalled();
        $execution->incrementProcessedItems(3)->shouldBeCalledOnce();

        $dispatcher->dispatch(Argument::any(), EventInterface::ITEM_STEP_AFTER_BATCH)->shouldBeCalled();
        $jobStopper->isStopping($execution)->willReturn(false);

        // second batch
        $processor->process('r4')->shouldBeCalled()->willThrow(
            new InvalidItemException('my msg', new FileInvalidItem(['r4'], 7))
        );
        $execution->incrementProcessedItems(1)->shouldBeCalledOnce();

        $warning = new Warning($execution->getWrappedObject(), 'my msg', [], ['r4']);
        $repository
            ->addWarning($warning)
            ->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), Argument::any())->shouldBeCalled();

        $processor->process(null)->shouldNotBeCalled();
        $writer->write(['p4'])->shouldNotBeCalled();

        $execution->getExitStatus()->willReturn($exitStatus);
        $exitStatus->getExitCode()->willReturn(ExitStatus::COMPLETED);
        $repository->updateStepExecution($execution)->shouldBeCalledTimes(5);
        $execution->isTerminateOnly()->willReturn(false);

        $execution->upgradeStatus(Argument::any())->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::STEP_EXECUTION_SUCCEEDED)->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::STEP_EXECUTION_COMPLETED)->shouldBeCalled();
        $execution->setEndTime(Argument::any())->shouldBeCalled();
        $execution->setExitStatus(Argument::any())->shouldBeCalled();

        $this->execute($execution);
    }

    function it_not_not_write_item_not_processed(
        ItemReaderInterface $reader,
        ItemProcessorInterface $processor,
        ItemWriterInterface $writer,
        EventDispatcherInterface $dispatcher,
        DoctrineJobRepository $repository,
        StepExecution $execution,
        BatchStatus $status,
        ExitStatus $exitStatus,
        JobStopper $jobStopper
    ) {
        $execution->getStatus()->willReturn($status);
        $status->getValue()->willReturn(BatchStatus::STARTING);

        $dispatcher->dispatch(Argument::any(), EventInterface::BEFORE_STEP_EXECUTION)->shouldBeCalled();
        $execution->setStartTime(Argument::any())->shouldBeCalled();
        $execution->setStatus(Argument::any())->shouldBeCalled();
        $jobStopper->isStopping($execution)->willReturn(false);

        // first batch
        $reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $processor->process('r1')->shouldBeCalled()->willReturn('p1');
        $processor->process('r2')->shouldBeCalled()->willReturn(null);
        $processor->process('r3')->shouldBeCalled()->willReturn('p3');
        $writer->write(['p1', 'p3'])->shouldBeCalled();
        $execution->incrementProcessedItems(3)->shouldBeCalledOnce();

        // second batch
        $processor->process('r4')->shouldBeCalled()->willReturn('p4');
        $execution->incrementProcessedItems(1)->shouldBeCalledOnce();

        $dispatcher->dispatch(Argument::any(), EventInterface::ITEM_STEP_AFTER_BATCH)->shouldBeCalledTimes(2);

        $processor->process(null)->shouldNotBeCalled();
        $writer->write(['p4'])->shouldBeCalled();

        $exitStatus->getExitCode()->willReturn(ExitStatus::COMPLETED);
        $execution->getExitStatus()->willReturn($exitStatus);
        $repository->updateStepExecution($execution)->shouldBeCalledTimes(5);
        $execution->isTerminateOnly()->willReturn(false);

        $execution->upgradeStatus(Argument::any())->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::STEP_EXECUTION_SUCCEEDED)->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::STEP_EXECUTION_COMPLETED)->shouldBeCalled();
        $execution->setEndTime(Argument::any())->shouldBeCalled();
        $execution->setExitStatus(Argument::any())->shouldBeCalled();

        $this->execute($execution);
    }
}
