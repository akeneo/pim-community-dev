<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Installer\Infrastructure\Job;

use Akeneo\Platform\Installer\Domain\Service\FilesystemPurgerInterface;
use Akeneo\Platform\Installer\Infrastructure\Job\PurgeFilesystemsTasklet;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;

class PurgeFilesystemsTaskletSpec extends ObjectBehavior
{
    public function let(
        FilesystemPurgerInterface $filesystemPurger,
        StepExecution $stepExecution,
    ) {
        $this->beConstructedWith($filesystemPurger);

        $this->setStepExecution($stepExecution);
    }

    public function it_is_a_tasklet(): void
    {
        $this->shouldHaveType(PurgeFilesystemsTasklet::class);
        $this->shouldImplement(TaskletInterface::class);
    }

    public function it_purge_filesystems(FilesystemPurgerInterface $filesystemPurger): void
    {
        $filesystemPurger->execute()->shouldBeCalled();
        $this->execute();
    }
}
