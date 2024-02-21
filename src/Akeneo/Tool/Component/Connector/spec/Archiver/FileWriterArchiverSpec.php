<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Archiver\ArchiverInterface;
use Akeneo\Tool\Component\Connector\Archiver\FileWriterArchiver;
use Akeneo\Tool\Component\Connector\Step\TaskletStep;
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
    public function let(
        FilesystemOperator $archivistFilesystem,
        JobRegistry $jobRegistry,
        FilesystemProvider $filesystemProvider,
        LoggerInterface $logger,
    ): void {
        $this->beConstructedWith($archivistFilesystem, $jobRegistry, $filesystemProvider, $logger);
    }

    public function it_is_an_archiver(): void
    {
        $this->shouldImplement(ArchiverInterface::class);
    }

    public function it_is_a_file_writer_archiver(): void
    {
        $this->shouldHaveType(FileWriterArchiver::class);
        $this->getName()->shouldBe('output');
    }

    public function it_supports_step_execution_when_the_step_is_an_item_step_with_usable_writer(
        JobRegistry $jobRegistry,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step1,
    ): void {
        $jobInstance->getJobName()->willReturn('export_job');

        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $step1->getName()->willReturn('step_1');
        $writer = $this->getUsableWriter();
        $step1->getWriter()->willReturn($writer);
        $job->getSteps()->willReturn([$step1]);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('step_1');

        $jobRegistry->get('export_job')->willReturn($job);

        $this->supports($stepExecution)->shouldReturn(true);
    }

    public function it_does_not_support_step_execution_when_the_step_is_an_item_step_with_not_usable_writer(
        JobRegistry $jobRegistry,
        ItemWriterInterface $writer,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step1,
    ): void {
        $jobInstance->getJobName()->willReturn('export_job');

        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $step1->getName()->willReturn('step_1');
        $step1->getWriter()->willReturn($writer);
        $job->getSteps()->willReturn([$step1]);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('step_1');

        $jobRegistry->get('export_job')->willReturn($job);

        $this->supports($stepExecution)->shouldReturn(false);
    }

    public function it_does_not_support_step_execution_when_the_job_does_not_exists(
        JobRegistry $jobRegistry,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
    ): void {
        $jobInstance->getJobName()->willReturn('export_job');

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $jobRegistry->get('export_job')->willThrow(\Exception::class);

        $this->supports($stepExecution)->shouldReturn(false);
    }

    public function it_does_not_support_step_execution_when_the_job_implementation_is_not_valid(
        JobRegistry $jobRegistry,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        JobInterface $job,
    ): void {
        $jobInstance->getJobName()->willReturn('export_job');

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $jobRegistry->get('export_job')->willReturn($job);

        $this->supports($stepExecution)->shouldReturn(false);
    }

    public function it_does_not_support_step_execution_when_no_step_is_found_in_the_job(
        JobRegistry $jobRegistry,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
    ): void
    {
        $jobInstance->getJobName()->willReturn('export_job');

        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $job->getSteps()->willReturn([]);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('step_1');

        $jobRegistry->get('export_job')->willReturn($job);

        $this->supports($stepExecution)->shouldReturn(false);
    }

    public function it_does_not_support_step_execution_when_more_than_one_step_is_found_in_the_job(
        JobRegistry $jobRegistry,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step1,
        ItemStep $step2,
    ): void
    {
        $jobInstance->getJobName()->willReturn('export_job');

        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $step1->getName()->willReturn('a_step');
        $step2->getName()->willReturn('a_step');
        $job->getSteps()->willReturn([$step1, $step2]);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('a_step');

        $jobRegistry->get('export_job')->willReturn($job);

        $this->supports($stepExecution)->shouldReturn(false);
    }

    public function it_does_not_support_step_execution_when_the_step_is_not_an_item_step(
        JobRegistry $jobRegistry,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        TaskletStep $step1,
    ): void {
        $jobInstance->getJobName()->willReturn('export_job');

        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $step1->getName()->willReturn('step_1');

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('step_1');

        $jobRegistry->get('export_job')->willReturn($job);

        $this->supports($stepExecution)->shouldReturn(false);
    }

    public function it_archives_written_files(
        FilesystemOperator $archivistFilesystem,
        JobRegistry $jobRegistry,
        FilesystemProvider $filesystemProvider,
        FilesystemOperator $catalogFilesystem,
        FilesystemOperator $localFilesystem,
        JobExecution $jobExecution,
        StepExecution $stepExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step1,
        ItemStep $step2,
    ): void {
        $jobInstance->getJobName()->willReturn('export_job');
        $jobInstance->getType()->willReturn('export');

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(1);

        $step1->getName()->willReturn('step_1');
        $writer = $this->getUsableWriter();
        $step1->getWriter()->willReturn($writer);
        $step2->getName()->willReturn('step_2');
        $step2->getWriter()->shouldNotBeCalled();
        $job->getSteps()->willReturn([$step1, $step2]);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('step_1');

        $jobRegistry->get('export_job')->willReturn($job);

        $writtenFiles = [
            WrittenFileInfo::fromFileStorage(
                'a/b/c/file.png',
                'catalogStorage',
                'files/my_media.png'
            ),
            WrittenFileInfo::fromLocalFile(
                '/tmp/export.csv',
                'export.csv',
            )
        ];
        $path = '/tmp/export.csv';
        $writer = $this->getUsableWriter($writtenFiles, $path);
        $step1->getWriter()->willReturn($writer);

        $filesystemProvider->getFilesystem('catalogStorage')->willReturn($catalogFilesystem);
        $imageStream = fopen('php://memory', 'r');
        $catalogFilesystem->readStream('a/b/c/file.png')->willReturn($imageStream);
        $archivistFilesystem->writeStream('export/export_job/1/output/files/my_media.png', $imageStream)->shouldBeCalled();

        $filesystemProvider->getFilesystem('localFilesystem')->willReturn($localFilesystem);
        $csvStream = fopen('php://memory', 'r');
        $localFilesystem->readStream('/tmp/export.csv')->willReturn($csvStream);
        $archivistFilesystem->writeStream('export/export_job/1/output/export.csv', $csvStream)->shouldBeCalled();

        $this->archive($stepExecution);
    }

    public function it_skips_past_steps(
        FilesystemOperator $archivistFilesystem,
        JobRegistry $jobRegistry,
        JobExecution $jobExecution,
        StepExecution $stepExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step1,
        ItemStep $step2,
    ): void {
        $jobInstance->getJobName()->willReturn('export_job');
        $jobInstance->getType()->willReturn('export');

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(1);

        $step1->getName()->willReturn('step_1');
        $step2->getName()->willReturn('step_2');
        $job->getSteps()->willReturn([$step1, $step2]);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('step_2');

        $jobRegistry->get('export_job')->willReturn($job);

        $writer = $this->getUsableWriter();
        $step1->getWriter()->shouldNotBeCalled();
        $step2->getWriter()->willReturn($writer);

        $archivistFilesystem->writeStream(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->archive($stepExecution);
    }

    public function it_logs_an_error_when_cannot_fetch_writing_file(
        FilesystemOperator $archivistFilesystem,
        JobRegistry $jobRegistry,
        FilesystemProvider $filesystemProvider,
        FilesystemOperator $catalogFilesystem,
        JobExecution $jobExecution,
        StepExecution $stepExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step1,
        ItemStep $step2,
        LoggerInterface $logger
    ): void {
        $jobInstance->getJobName()->willReturn('export_job');
        $jobInstance->getType()->willReturn('export');

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(1);

        $step1->getName()->willReturn('step_1');
        $writer = $this->getUsableWriter();
        $step1->getWriter()->willReturn($writer);
        $step2->getName()->willReturn('step_2');
        $step2->getWriter()->shouldNotBeCalled();
        $job->getSteps()->willReturn([$step1, $step2]);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('step_1');

        $jobRegistry->get('export_job')->willReturn($job);

        $writtenFiles = [
            WrittenFileInfo::fromFileStorage(
                'a/b/c/non_existing_file.png',
                'catalogStorage',
                'files/non_existing_file.png'
            ),
            WrittenFileInfo::fromFileStorage(
                'a/b/c/file.png',
                'catalogStorage',
                'files/my_media.png'
            )
        ];
        $writer = $this->getUsableWriter($writtenFiles);
        $step1->getWriter()->willReturn($writer);

        $filesystemProvider->getFilesystem('catalogStorage')->willReturn($catalogFilesystem);
        $catalogFilesystem->readStream('a/b/c/non_existing_file.png')->willThrow(UnableToReadFile::class);
        $imageStream = fopen('php://memory', 'r');
        $catalogFilesystem->readStream('a/b/c/file.png')->willReturn($imageStream);

        $archivistFilesystem->writeStream('export/export_job/1/output/files/my_media.png', $imageStream)->shouldBeCalled();
        $archivistFilesystem->writeStream('export/export_job/1/output/non_existing_file.csv', Argument::type('resource'))->shouldNotBeCalled();

        $logger->warning(
            'The remote file could not be read from the remote filesystem',
            Argument::type('array'),
        )->shouldBeCalled();

        $this->archive($stepExecution);
    }

    public function it_gets_the_archives_for_a_job_execution(
        FilesystemOperator $archivistFilesystem,
        JobRegistry $jobRegistry,
        JobExecution $jobExecution,
        StepExecution $stepExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step1
    ): void {
        $jobInstance->getJobName()->willReturn('export_job');
        $jobInstance->getType()->willReturn('export');

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(1);

        $step1->getName()->willReturn('step_1');
        $writer = $this->getUsableWriter([], '/tmp/export.csv');
        $step1->getWriter()->willReturn($writer);
        $job->getSteps()->willReturn([$step1]);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('step_1');

        $jobRegistry->get('export_job')->willReturn($job);

        $jobExecution->getStepExecutions()->willReturn([$stepExecution]);

        $archivistFilesystem->listContents('export/export_job/1/output', false)->shouldBeCalled()->willReturn(
            new DirectoryListing(
                [
                    new FileAttributes('export/export_job/1/output/export_1.csv'),
                    new FileAttributes('export/export_job/1/output/export_2.csv'),
                    new FileAttributes('export/export_job/1/output/files/sku1/image.jpg'),
                    new FileAttributes('export/export_job/1/output/files/sku2/media.png'),
                ]
            )
        );

        $this->getArchives($jobExecution)->shouldYield([
            'export_1.csv' => 'export/export_job/1/output/export_1.csv',
            'export_2.csv' => 'export/export_job/1/output/export_2.csv',
            'files/sku1/image.jpg' => 'export/export_job/1/output/files/sku1/image.jpg',
            'files/sku2/media.png' => 'export/export_job/1/output/files/sku2/media.png',
        ]);
    }

    public function it_returns_the_archivist_directory_from_job_execution(
        JobExecution $jobExecution,
        JobInstance $jobInstance,
    ): void {
        $jobInstance->getJobName()->willReturn('csv_product_export');
        $jobInstance->getType()->willReturn('export');
        $jobExecution->getId()->willReturn(14);
        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $this->getArchiveDirectoryPath($jobExecution)->shouldReturn('export/csv_product_export/14/output');
    }

    private function getUsableWriter(array $writtenFiles = [], string $path = ''): ArchivableWriterInterface
    {
        return new class($writtenFiles, $path) implements ItemWriterInterface, ArchivableWriterInterface {
            public function __construct(
                private readonly array $writtenFiles,
                private readonly string $path,
            ) {
            }

            public function getWrittenFiles(): array
            {
                return $this->writtenFiles;
            }

            public function getPath(): string
            {
                return $this->path;
            }

            public function write(array $items)
            {
            }
        };
    }
}
