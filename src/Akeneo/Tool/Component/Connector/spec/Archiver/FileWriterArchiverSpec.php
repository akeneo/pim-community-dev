<?php

namespace spec\Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Batch\Step\StepInterface;
use Akeneo\Tool\Component\Connector\Archiver\ArchiverInterface;
use Akeneo\Tool\Component\Connector\Archiver\FileWriterArchiver;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToReadFile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class FileWriterArchiverSpec extends ObjectBehavior
{
    function let(
        FilesystemOperator $filesystem,
        JobRegistry $jobRegistry,
        FilesystemProvider $filesystemProvider,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        StepExecution $stepExecution,
        LoggerInterface $logger
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
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
        StepExecution $stepExecution,
        JobInstance $jobInstance,
        Job $job,
        StepInterface $step
    ) {
        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobRegistry->get('my_job_name')->willReturn($job);
        $job->getSteps()->willReturn([$step]);

        $this->supports($stepExecution)->shouldReturn(false);
    }

    function it_does_not_support_jobs_without_archivable_file_writer(
        JobRegistry $jobRegistry,
        JobExecution $jobExecution,
        StepExecution $stepExecution,
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

        $this->supports($stepExecution)->shouldReturn(false);
    }

    function it_stores_written_files(
        FilesystemOperator $filesystem,
        JobRegistry $jobRegistry,
        FilesystemProvider $filesystemProvider,
        FilesystemOperator $catalogFilesystem,
        JobExecution $jobExecution,
        StepExecution $stepExecution,
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
        $stepExecution->getStepName()->willReturn('export');
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

        $itemStep->getName()->willReturn('export');
        $itemStep->getWriter()->willReturn($writer);
        $job->getSteps()->willReturn([$itemStep]);
        $this->supports($stepExecution)->shouldReturn(true);

        $filesystemProvider->getFilesystem('catalogStorage')->shouldBeCalled()->willReturn($catalogFilesystem);
        $remoteStream = \tmpfile();
        $catalogFilesystem->readStream('a/b/c/file.png')->willReturn($remoteStream);

        $filesystem->writeStream(
            'export/my_job_name/42/output/files/my_media.png',
            $remoteStream
        )->shouldBeCalled();
        $filesystem->writeStream('export/my_job_name/42/output/export.csv', Argument::type('resource'))->shouldBeCalled();

        $this->archive($stepExecution);
        if (\is_resource($remoteStream)) {
            \fclose($remoteStream);
        }
        \unlink('/tmp/spec/export.csv');
    }

    function it_log_an_error_when_cannot_fetch_writing_file(
        FilesystemOperator $filesystem,
        JobRegistry $jobRegistry,
        FilesystemProvider $filesystemProvider,
        FilesystemOperator $catalogFilesystem,
        JobExecution $jobExecution,
        StepExecution $stepExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $itemStep,
        LoggerInterface $logger
    ) {
        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobInstance->getType()->willReturn('export');
        $jobExecution->getId()->willReturn(42);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getStepName()->willReturn('export');
        $jobRegistry->get('my_job_name')->willReturn($job);

        $writer = new class implements ItemWriterInterface, ArchivableWriterInterface {
            public function getWrittenFiles(): array {
                return [
                    WrittenFileInfo::fromFileStorage(
                        'a/b/c/non_existing_file.png',
                        'catalogStorage',
                        'files/non_existing_file.png'
                    ),
                    WrittenFileInfo::fromFileStorage(
                        'a/b/c/file.png',
                        'catalogStorage',
                        'files/my_media.png'
                    ),
                ];
            }
            public function getPath(): string
            {
                return '/tmp/spec/export.csv';
            }
            public function write($items): void {}
        };

        $itemStep->getWriter()->willReturn($writer);
        $itemStep->getName()->willReturn('export');
        $job->getSteps()->willReturn([$itemStep]);
        $this->supports($stepExecution)->shouldReturn(true);

        $filesystemProvider->getFilesystem('catalogStorage')->shouldBeCalled()->willReturn($catalogFilesystem);
        $remoteStream = \tmpfile();
        $catalogFilesystem->readStream('a/b/c/non_existing_file.png')->willThrow(UnableToReadFile::class);
        $catalogFilesystem->readStream('a/b/c/file.png')->willReturn($remoteStream);

        $filesystem->writeStream(
            'export/my_job_name/42/output/files/my_media.png',
            $remoteStream
        )->shouldBeCalled();

        $filesystem->writeStream(
            'export/my_job_name/42/output/non_existing_file.csv',
            Argument::type('resource')
        )->shouldNotBeCalled();

        $logger->warning(
            'The remote file could not be read from the remote filesystem',
            Argument::type('array')
        )->shouldBeCalled();

        $this->archive($stepExecution);
    }

    function it_gets_the_archives_for_a_job_execution(
        FilesystemOperator $filesystem,
        JobRegistry $jobRegistry,
        JobExecution $jobExecution,
        StepExecution $stepExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $itemStep
    ) {
        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobInstance->getType()->willReturn('export');
        $jobExecution->getId()->willReturn(42);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getStepName()->willReturn('export');
        $jobRegistry->get('my_job_name')->willReturn($job);

        $writer = new class implements ItemWriterInterface, ArchivableWriterInterface {
            public function getWrittenFiles(): array
            {
                return [];
            }

            public function getPath(): string
            {
                return '/tmp/spec/export.csv';
            }

            public function write($items): void
            {
            }
        };
        $itemStep->getWriter()->willReturn($writer);
        $itemStep->getName()->willReturn('export');
        $job->getSteps()->willReturn([$itemStep]);
        $jobExecution->getStepExecutions()->willReturn([$stepExecution]);
        $this->supports($stepExecution)->shouldReturn(true);

        $filesystem->listContents('export/my_job_name/42/output', false)->shouldBeCalled()->willReturn(
            new DirectoryListing(
                [
                    new FileAttributes('export/my_job_name/42/output/export_1.csv'),
                    new FileAttributes('export/my_job_name/42/output/export_2.csv'),
                    new FileAttributes('export/my_job_name/42/output/files/sku1/image.jpg'),
                    new FileAttributes('export/my_job_name/42/output/files/sku2/media.png'),
                ]
            )
        );

        $this->getArchives($jobExecution)->shouldYield([
            'export_1.csv' => 'export/my_job_name/42/output/export_1.csv',
            'export_2.csv' => 'export/my_job_name/42/output/export_2.csv',
            'files/sku1/image.jpg' => 'export/my_job_name/42/output/files/sku1/image.jpg',
            'files/sku2/media.png' => 'export/my_job_name/42/output/files/sku2/media.png',
        ]);
    }

    function it_return_the_archivist_directory_from_job_execution(
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $jobInstance->getJobName()->willReturn('csv_product_export');
        $jobInstance->getType()->willReturn('export');
        $jobExecution->getId()->willReturn(12);

        $this->getArchiveDirectoryPath($jobExecution)->shouldReturn('export/csv_product_export/12/output');
    }
}
