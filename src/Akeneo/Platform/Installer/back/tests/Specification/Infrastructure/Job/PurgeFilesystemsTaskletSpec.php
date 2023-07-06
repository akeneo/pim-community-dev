<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Installer\Infrastructure\Job;

use Akeneo\Platform\Installer\Domain\Service\FilesystemPurgerInterface;
use Akeneo\Platform\Installer\Infrastructure\Job\PurgeFilesystemsTasklet;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use League\Flysystem\FilesystemOperator;
use PhpSpec\ObjectBehavior;

class PurgeFilesystemsTaskletSpec extends ObjectBehavior
{
    public function let(
        FilesystemPurgerInterface $filesystemPurger,
        FilesystemOperator $filesystem1,
        FilesystemOperator $filesystem2,
        StepExecution $stepExecution,
        JobStopper $jobStopper,
    ) {
        $this->beConstructedWith($filesystemPurger, [$filesystem1, $filesystem2], $jobStopper);

        $this->setStepExecution($stepExecution);
    }

    public function it_is_a_tasklet(): void
    {
        $this->shouldHaveType(PurgeFilesystemsTasklet::class);
        $this->shouldImplement(TaskletInterface::class);
    }

    public function it_purge_filesystems(
        FilesystemPurgerInterface $filesystemPurger,
        FilesystemOperator $filesystem1,
        FilesystemOperator $filesystem2,
        StepExecution $stepExecution,
        JobStopper $jobStopper,
    ): void {
        $stepExecution->getCurrentState()->willReturn([]);
        $jobStopper->isPausing($stepExecution)->willReturn(false);

        $filesystemPurger->purge($filesystem1)->shouldBeCalled();
        $filesystemPurger->purge($filesystem2)->shouldBeCalled();

        $this->execute();
    }

    public function it_can_be_paused(
        FilesystemPurgerInterface $filesystemPurger,
        FilesystemOperator $filesystem1,
        FilesystemOperator $filesystem2,
        StepExecution $stepExecution,
        JobStopper $jobStopper,
    ): void {
        $stepExecution->getCurrentState()->willReturn([]);
        $jobStopper->isPausing($stepExecution)->shouldBeCalledTimes(2)->willReturn(false, true);

        $filesystemPurger->purge($filesystem1)->shouldBeCalled();
        $jobStopper->pause($stepExecution, ['0',])->shouldBeCalled();
        $filesystemPurger->purge($filesystem2)->shouldNotBeCalled();

        $this->execute();
    }

    public function it_executes_remaining_filesystems_after_paused_job(
        FilesystemPurgerInterface $filesystemPurger,
        FilesystemOperator $filesystem1,
        FilesystemOperator $filesystem2,
        StepExecution $stepExecution,
        JobStopper $jobStopper,
    ): void {
        $stepExecution->getCurrentState()->willReturn(['0',]);
        $jobStopper->isPausing($stepExecution)->willReturn(false);

        $filesystemPurger->purge($filesystem1)->shouldNotBeCalled();
        $filesystemPurger->purge($filesystem2)->shouldBeCalled();

        $this->execute();
    }
}
