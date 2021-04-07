<?php

namespace spec\Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Batch\Step\StepInterface;
use Akeneo\Tool\Component\Connector\Archiver\ArchiverInterface;
use Akeneo\Tool\Component\Connector\Archiver\FileWriterArchiver;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class FileWriterArchiverSpec extends ObjectBehavior
{
    function let(
        Filesystem $filesystem,
        JobRegistry $jobRegistry,
        FilesystemProvider $filesystemProvider,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($filesystem, $jobRegistry, $filesystemProvider, $logger);
    }

    function it_is_an_archiver()
    {
        $this->shouldImplement(ArchiverInterface::class);
    }

    function it_is_a_file_writer_archiver()
    {
        $this->shouldHaveType(FileWriterArchiver::class);
        $this->getName()->shouldBe('output');
    }

    function it_does_not_support_jobs_without_item_step(
        JobRegistry $jobRegistry,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        StepInterface $step
    ) {
        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobRegistry->get('my_job_name')->willReturn($job);
        $job->getSteps()->willReturn([$step]);

        $this->supports($jobExecution)->shouldReturn(false);
    }

    function it_does_not_support_jobs_without_archivable_file_writer(
        JobRegistry $jobRegistry,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $itemStep,
        ItemWriterInterface $itemWriter
    ) {
        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobRegistry->get('my_job_name')->willReturn($job);

        $itemStep->getWriter()->willReturn($itemWriter);
        $job->getSteps()->willReturn([$itemStep]);

        $this->supports($jobExecution)->shouldReturn(false);
    }

    function it_does_not_support_jobs_without_written_file(
        JobRegistry $jobRegistry,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $itemStep
    ) {
        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobRegistry->get('my_job_name')->willReturn($job);

        $writer = new class implements ItemWriterInterface, ArchivableWriterInterface {
            public function getWrittenFiles(): array {
                return [];
            }
            public function getPath(): string
            {
                return '/tmp/my_job_name.csv';
            }
            public function write($items): void {}
        };
        $itemStep->getWriter()->willReturn($writer);
        $job->getSteps()->willReturn([$itemStep]);

        $this->supports($jobExecution)->shouldReturn(false);
    }

    function it_stores_written_files(
        FilesystemInterface $filesystem,
        JobRegistry $jobRegistry,
        FilesystemProvider $filesystemProvider,
        FilesystemInterface $catalogFilesystem,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $itemStep
    ) {
        if (!is_dir('/tmp/spec')) {
            mkdir('/tmp/spec');
        }
        \touch('/tmp/spec/export.csv');

        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobInstance->getType()->willReturn('export');
        $jobExecution->getId()->willReturn(42);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobRegistry->get('my_job_name')->willReturn($job);

        $writer = new class implements ItemWriterInterface, ArchivableWriterInterface {
            public function getWrittenFiles(): array {
                return [
                    WrittenFileInfo::fromFileStorage(
                        'a/b/c/file.png',
                        'catalogStorage',
                        'files/my_media.png'
                    ),
                    WrittenFileInfo::fromLocalFile(
                        '/tmp/spec/export.csv',
                        'export.csv',
                    )
                ];
            }
            public function getPath(): string
            {
                return '/tmp/spec/export.csv';
            }
            public function write($items): void {}
        };

        $itemStep->getWriter()->willReturn($writer);
        $job->getSteps()->willReturn([$itemStep]);
        $this->supports($jobExecution)->shouldReturn(true);

        $filesystemProvider->getFilesystem('catalogStorage')->shouldBeCalled()->willReturn($catalogFilesystem);
        $remoteStream = \tmpfile();
        $catalogFilesystem->readStream('a/b/c/file.png')->willReturn($remoteStream);

        $filesystem->putStream(
            'export/my_job_name/42/output/files/my_media.png',
            $remoteStream
        )->shouldBeCalled();
        $filesystem->putStream('export/my_job_name/42/output/export.csv', Argument::type('resource'))->shouldBeCalled();

        $this->archive($jobExecution);
        if (\is_resource($remoteStream)) {
            \fclose($remoteStream);
        }
        \unlink('/tmp/spec/export.csv');
    }
}
