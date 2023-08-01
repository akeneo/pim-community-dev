<?php

namespace spec\Akeneo\Tool\Component\Batch\Step;

use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Item\StatefulInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Job\JobStopperInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;
use Akeneo\Tool\Component\Batch\spec\Step\StatefulReaderInterface;
use Akeneo\Tool\Component\Batch\spec\Step\StatefulWriterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ItemStepSpec extends ObjectBehavior
{
    function let(
        EventDispatcherInterface $dispatcher,
        DoctrineJobRepository $repository,
        PausableReader $reader,
        ItemProcessorInterface $processor,
        PausableWriter $writer,
        JobStopperInterface $jobStopper
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
        JobStopper $jobStopper
    ) {
        $reader->setState([])->shouldBeCalled();
        $writer->setState([])->shouldBeCalled();
        $execution->getStatus()->willReturn(new BatchStatus(BatchStatus::STARTING));

        $dispatcher->dispatch(Argument::any(), EventInterface::BEFORE_STEP_EXECUTION)->shouldBeCalled();
        $execution->setStatus(Argument::any())->shouldBeCalled();
        $execution->getCurrentState()->willReturn([]);

        // first batch
        $reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $processor->process('r1')->shouldBeCalled()->willReturn('p1');
        $processor->process('r2')->shouldBeCalled()->willReturn('p2');
        $processor->process('r3')->shouldBeCalled()->willReturn('p3');
        $writer->write(['p1', 'p2', 'p3'])->shouldBeCalled();
        $execution->incrementProcessedItems(3)->shouldBeCalledOnce();

        $dispatcher->dispatch(Argument::any(), EventInterface::ITEM_STEP_AFTER_BATCH)->shouldBeCalled();
        $jobStopper->isStopping($execution)->willReturn(false);
        $jobStopper->isPausing($execution)->willReturn(false);

        // second batch
        $processor->process('r4')->shouldBeCalled()->willReturn('p4');
        $processor->process(null)->shouldNotBeCalled();
        $writer->write(['p4'])->shouldBeCalled();
        $execution->incrementProcessedItems(1)->shouldBeCalledOnce();

        $dispatcher->dispatch(Argument::any(), EventInterface::ITEM_STEP_AFTER_BATCH)->shouldBeCalled();
        $jobStopper->isStopping($execution)->willReturn(false);
        $jobStopper->isPausing($execution)->willReturn(false);

        $writer->flush()->shouldBeCalledOnce();

        $exitStatus = new ExitStatus(ExitStatus::COMPLETED, "");
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

    function it_executes_with_an_invalid_item_during_processing(
        $reader,
        $processor,
        $writer,
        $dispatcher,
        $repository,
        StepExecution $execution,
        JobStopper $jobStopper
    ) {
        $reader->setState([])->shouldBeCalled();
        $writer->setState([])->shouldBeCalled();

        $execution->getStatus()->willReturn(new BatchStatus(BatchStatus::STARTING));

        $dispatcher->dispatch(Argument::any(), EventInterface::BEFORE_STEP_EXECUTION)->shouldBeCalled();
        $execution->setStatus(Argument::any())->shouldBeCalled();
        $execution->getCurrentState()->willReturn([]);

        // first batch
        $reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $processor->process('r1')->shouldBeCalled()->willReturn('p1');
        $processor->process('r2')->shouldBeCalled()->willReturn('p2');
        $processor->process('r3')->shouldBeCalled()->willReturn('p3');
        $writer->write(['p1', 'p2', 'p3'])->shouldBeCalled();
        $execution->incrementProcessedItems(3)->shouldBeCalledOnce();

        $dispatcher->dispatch(Argument::any(), EventInterface::ITEM_STEP_AFTER_BATCH)->shouldBeCalled();
        $jobStopper->isStopping($execution)->willReturn(false);
        $jobStopper->isPausing($execution)->willReturn(false);

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

        $writer->flush()->shouldBeCalledOnce();

        $exitStatus = new ExitStatus(ExitStatus::COMPLETED, "");
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

    function it_not_not_write_item_not_processed(
        ItemReaderInterface $reader,
        ItemProcessorInterface $processor,
        ItemWriterInterface $writer,
        EventDispatcherInterface $dispatcher,
        DoctrineJobRepository $repository,
        StepExecution $execution,
        JobStopper $jobStopper
    ) {
        $reader->setState([])->shouldBeCalled();
        $writer->setState([])->shouldBeCalled();

        $execution->getStatus()->willReturn(new BatchStatus(BatchStatus::STARTING));

        $dispatcher->dispatch(Argument::any(), EventInterface::BEFORE_STEP_EXECUTION)->shouldBeCalled();
        $execution->setStatus(Argument::any())->shouldBeCalled();
        $execution->getCurrentState()->willReturn([]);

        $jobStopper->isStopping($execution)->willReturn(false);
        $jobStopper->isPausing($execution)->willReturn(false);

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

        $writer->flush()->shouldBeCalledOnce();

        $exitStatus = new ExitStatus(ExitStatus::COMPLETED, "");
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

    function it_stop_if_asked(
        ItemReaderInterface $reader,
        ItemProcessorInterface $processor,
        ItemWriterInterface $writer,
        EventDispatcherInterface $dispatcher,
        DoctrineJobRepository $repository,
        StepExecution $execution,
        JobStopper $jobStopper
    ) {
        $reader->setState([])->shouldBeCalled();
        $writer->setState([])->shouldBeCalled();

        $execution->getStatus()->willReturn(new BatchStatus(BatchStatus::STARTING));

        $dispatcher->dispatch(Argument::any(), EventInterface::BEFORE_STEP_EXECUTION)->shouldBeCalled();
        $execution->setStatus(Argument::any())->shouldBeCalled();
        $execution->getCurrentState()->willReturn([]);

        $jobStopper->isStopping($execution)->willReturn(false);
        $jobStopper->isPausing($execution)->willReturn(false);

        // first batch
        $reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $processor->process('r1')->shouldBeCalled()->willReturn('p1');
        $processor->process('r2')->shouldBeCalled()->willReturn(null);
        $processor->process('r3')->shouldBeCalled()->willReturn('p3');
        $writer->write(['p1', 'p3'])->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::ITEM_STEP_AFTER_BATCH)->shouldBeCalledOnce();
        $execution->incrementProcessedItems(3)->shouldBeCalledOnce();

        // second batch
        $jobStopper->isStopping($execution)->willReturn(true);
        $jobStopper->stop($execution)->shouldBeCalled();

        $writer->flush()->shouldBeCalledOnce();

        $exitStatus = new ExitStatus(ExitStatus::STOPPED, "");
        $execution->getExitStatus()->willReturn($exitStatus);

        $repository->updateStepExecution($execution)->shouldBeCalledTimes(4);
        $execution->isTerminateOnly()->willReturn(false);

        $execution->upgradeStatus(Argument::any())->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::STEP_EXECUTION_SUCCEEDED)->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::STEP_EXECUTION_COMPLETED)->shouldBeCalled();
        $execution->setEndTime(Argument::any())->shouldBeCalled();
        $execution->setExitStatus(Argument::any())->shouldBeCalled();

        $this->execute($execution);
    }

    function it_pause_if_asked(
        StatefulReaderInterface $reader,
        ItemProcessorInterface $processor,
        StatefulWriterInterface $writer,
        EventDispatcherInterface $dispatcher,
        DoctrineJobRepository $repository,
        StepExecution $execution,
        JobStopper $jobStopper
    ) {
        $reader->setState([])->shouldBeCalled();
        $writer->setState([])->shouldBeCalled();

        $execution->getStatus()->willReturn(new BatchStatus(BatchStatus::STARTING));

        $dispatcher->dispatch(Argument::any(), EventInterface::BEFORE_STEP_EXECUTION)->shouldBeCalled();
        $execution->setStatus(Argument::any())->shouldBeCalled();
        $execution->getCurrentState()->willReturn([]);
        $reader->setState([])->shouldBeCalled();
        $writer->setState([])->shouldBeCalled();
        $jobStopper->isStopping($execution)->willReturn(false);
        $jobStopper->isPausing($execution)->willReturn(false);

        // first batch
        $reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $processor->process('r1')->shouldBeCalled()->willReturn('p1');
        $processor->process('r2')->shouldBeCalled()->willReturn(null);
        $processor->process('r3')->shouldBeCalled()->willReturn('p3');
        $writer->write(['p1', 'p3'])->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::ITEM_STEP_AFTER_BATCH)->shouldBeCalledOnce();
        $execution->incrementProcessedItems(3)->shouldBeCalledOnce();

        // second batch
        $jobStopper->isPausing($execution)->willReturn(true);
        $reader->getState()->willReturn(['position' => 1]);
        $writer->getState()->willReturn(['file_path' => '/tmp/file.xslx']);
        $jobStopper->pause($execution, ['reader' => ['position' => 1], 'writer' => ['file_path' => '/tmp/file.xslx']])->shouldBeCalled();

        $writer->flush()->shouldNotBeCalled();

        $exitStatus = new ExitStatus(ExitStatus::COMPLETED, "");
        $execution->getExitStatus()->willReturn($exitStatus);

        $repository->updateStepExecution($execution)->shouldBeCalledTimes(4);
        $execution->isTerminateOnly()->willReturn(false);

        $execution->upgradeStatus(Argument::any())->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::STEP_EXECUTION_SUCCEEDED)->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::STEP_EXECUTION_COMPLETED)->shouldBeCalled();
        $execution->setEndTime(Argument::any())->shouldBeCalled();
        $execution->setExitStatus(Argument::any())->shouldBeCalled();

        $this->execute($execution);
    }

    function it_resumes_paused_job_with_success(
        $reader,
        $processor,
        $writer,
        $dispatcher,
        $repository,
        StepExecution $execution,
        JobStopper $jobStopper
    ) {
        $reader->setState([])->shouldBeCalled();
        $writer->setState([])->shouldBeCalled();

        $execution->getStatus()->willReturn(new BatchStatus(BatchStatus::PAUSED));

        $dispatcher->dispatch(Argument::any(), EventInterface::BEFORE_STEP_EXECUTION_RESUME)->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::BEFORE_STEP_EXECUTION)->shouldBeCalled();
        $execution->setStatus(Argument::that(fn (BatchStatus $newStatus) => $execution->getStatus()->willReturn($newStatus)))->shouldBeCalled();
        $execution->getCurrentState()->willReturn([]);

        // first batch
        $reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $processor->process('r1')->shouldBeCalled()->willReturn('p1');
        $processor->process('r2')->shouldBeCalled()->willReturn('p2');
        $processor->process('r3')->shouldBeCalled()->willReturn('p3');
        $writer->write(['p1', 'p2', 'p3'])->shouldBeCalled();
        $execution->incrementProcessedItems(3)->shouldBeCalledOnce();

        $dispatcher->dispatch(Argument::any(), EventInterface::ITEM_STEP_AFTER_BATCH)->shouldBeCalled();
        $jobStopper->isStopping($execution)->willReturn(false);
        $jobStopper->isPausing($execution)->willReturn(false);

        // second batch
        $processor->process('r4')->shouldBeCalled()->willReturn('p4');
        $processor->process(null)->shouldNotBeCalled();
        $writer->write(['p4'])->shouldBeCalled();
        $execution->incrementProcessedItems(1)->shouldBeCalledOnce();

        $dispatcher->dispatch(Argument::any(), EventInterface::ITEM_STEP_AFTER_BATCH)->shouldBeCalled();
        $jobStopper->isStopping($execution)->willReturn(false);
        $jobStopper->isPausing($execution)->willReturn(false);

        $writer->flush()->shouldBeCalledOnce();

        $exitStatus = new ExitStatus(ExitStatus::COMPLETED, "");
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

    function it_executes_a_job_and_set_as_paused(
        $reader,
        $processor,
        $writer,
        $dispatcher,
        $repository,
        StepExecution $execution,
        JobStopper $jobStopper
    ) {
        $reader->setState([])->shouldBeCalled();
        $writer->setState([])->shouldBeCalled();

        $pausedStatus = new BatchStatus(BatchStatus::PAUSED);
        $execution->getStatus()->willReturn($pausedStatus);
        $dispatcher->dispatch(Argument::any(), EventInterface::BEFORE_STEP_EXECUTION_RESUME)->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::BEFORE_STEP_EXECUTION)->shouldBeCalled();
        $execution->setStatus(Argument::that(fn (BatchStatus $newStatus) => $execution->getStatus()->willReturn($newStatus)))->shouldBeCalled();
        $execution->getCurrentState()->willReturn([]);

        // first batch
        $reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $processor->process('r1')->shouldBeCalled()->willReturn('p1');
        $processor->process('r2')->shouldBeCalled()->willReturn('p2');
        $processor->process('r3')->shouldBeCalled()->willReturn('p3');
        $writer->write(['p1', 'p2', 'p3'])->shouldBeCalled();
        $execution->incrementProcessedItems(3)->shouldBeCalledOnce();

        $dispatcher->dispatch(Argument::any(), EventInterface::ITEM_STEP_AFTER_BATCH)->shouldBeCalled();
        $jobStopper->isStopping($execution)->willReturn(false);
        $jobStopper->isPausing($execution)->willReturn(true);
        $jobStopper->pause($execution, ['reader' => [], 'writer' => []]);

        $reader->getState()->willReturn([]);
        $writer->getState()->willReturn([]);

        $writer->flush()->shouldNotBeCalled();

        $exitStatus = new ExitStatus(ExitStatus::COMPLETED, "");
        $execution->getExitStatus()->willReturn($exitStatus);
        $repository->updateStepExecution($execution)->shouldBeCalledTimes(4);
        $execution->isTerminateOnly()->willReturn(false);

        $execution->upgradeStatus(Argument::that(fn () => $execution->getStatus()->willReturn($pausedStatus)))->shouldBeCalled();

        $dispatcher->dispatch(Argument::any(), EventInterface::STEP_EXECUTION_SUCCEEDED)->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::STEP_EXECUTION_COMPLETED)->shouldBeCalled();
        $execution->setEndTime(Argument::any())->shouldNotBeCalled();
        $execution->setExitStatus(Argument::any())->shouldBeCalled();

        $this->execute($execution);
    }

    function it_flushes_step_elements_when_job_is_pausing_and_every_items_are_processed(
        StatefulReaderInterface $reader,
        ItemProcessorInterface $processor,
        PausableWriter $writer,
        EventDispatcherInterface $dispatcher,
        DoctrineJobRepository $repository,
        StepExecution $execution,
        JobStopper $jobStopper
    ) {
        $execution->getStatus()->willReturn(new BatchStatus(BatchStatus::STARTING));

        $dispatcher->dispatch(Argument::any(), EventInterface::BEFORE_STEP_EXECUTION)->shouldBeCalled();
        $execution->setStatus(Argument::any())->shouldBeCalled();

        $execution->getCurrentState()->willReturn([]);
        $reader->setState([])->shouldBeCalled();
        $writer->setState([])->shouldBeCalled();

        $jobStopper->isStopping($execution)->willReturn(false);
        $jobStopper->isPausing($execution)->willReturn(false);

        $reader->read()->willReturn('r1', 'r2', 'r3', null);
        $processor->process('r1')->shouldBeCalled()->willReturn('p1');
        $processor->process('r2')->shouldBeCalled()->willReturn('p2');
        $processor->process('r3')->shouldBeCalled()->willReturn('p3');
        $writer->write(['p1', 'p2', 'p3'])->shouldBeCalledOnce();

        $dispatcher->dispatch(Argument::any(), EventInterface::ITEM_STEP_AFTER_BATCH)->shouldBeCalledOnce();
        $execution->incrementProcessedItems(3)->shouldBeCalledOnce();

        $writer->flush()->shouldBeCalledOnce();

        $exitStatus = new ExitStatus(ExitStatus::COMPLETED, "");
        $execution->getExitStatus()->willReturn($exitStatus);

        $repository->updateStepExecution($execution)->shouldBeCalledTimes(4);
        $execution->isTerminateOnly()->willReturn(false);

        $execution->upgradeStatus(Argument::any())->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::STEP_EXECUTION_SUCCEEDED)->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EventInterface::STEP_EXECUTION_COMPLETED)->shouldBeCalled();
        $execution->setEndTime(Argument::any())->shouldBeCalled();
        $execution->setExitStatus(Argument::any())->shouldBeCalled();

        $this->execute($execution);
    }
}

interface PausableReader extends StatefulInterface, ItemReaderInterface
{

}

interface PausableWriter extends StatefulInterface, ItemWriterInterface, FlushableInterface
{

}
