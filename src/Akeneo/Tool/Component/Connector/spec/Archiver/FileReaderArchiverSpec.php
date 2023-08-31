<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Archiver\FileReaderArchiver;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use Akeneo\Tool\Component\Connector\Step\TaskletStep;
use League\Flysystem\FilesystemOperator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileReaderArchiverSpec extends ObjectBehavior
{
    public function let(
        FilesystemOperator $localFilesystem,
        FilesystemOperator $archivistFilesystem,
        JobRegistry $jobRegistry,
    ): void {
        $this->beConstructedWith($localFilesystem, $archivistFilesystem, $jobRegistry);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FileReaderArchiver::class);
    }

    public function it_returns_the_name_of_the_archiver(): void
    {
        $this->getName()->shouldReturn('input');
    }

    public function it_archives_a_file_when_reader_is_valid(
        FilesystemOperator $localFilesystem,
        FilesystemOperator $archivistFilesystem,
        JobRegistry $jobRegistry,
        FileReaderInterface $reader,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step1,
        ItemStep $step2,
        JobParameters $jobParameters,
    ): void {
        $jobInstance->getJobName()->willReturn('import_job');
        $jobInstance->getType()->willReturn('import');

        $jobParameters->has('storage')->willReturn(true);
        $jobParameters->get('storage')->willReturn(['type' => 'local', 'file_path' => '/tmp/import.xlsx']);

        $jobExecution->getId()->willReturn(1);
        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('step_1');

        $step1->getName()->willReturn('step_1');
        $step1->getReader()->willReturn($reader);
        $step2->getName()->willReturn('step_2');
        $step2->getReader()->shouldNotBeCalled();
        $job->getSteps()->willReturn([$step1, $step2]);

        $jobRegistry->get('import_job')->willReturn($job);

        $expectedStream = fopen('php://memory', 'r');
        $localFilesystem->fileExists('/tmp/import.xlsx')->willReturn(true);
        $localFilesystem->readStream('/tmp/import.xlsx')->willReturn($expectedStream);

        $expectedFilePath = 'import/import_job/1/input/import.xlsx';
        $archivistFilesystem->writeStream($expectedFilePath, $expectedStream)->shouldBeCalledOnce();

        $this->archive($stepExecution);
    }

    public function it_does_not_archive_a_file_if_step_is_not_an_item_step(
        FilesystemOperator $archivistFilesystem,
        JobRegistry $jobRegistry,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        TaskletStep $step1,
    ): void {
        $jobInstance->getJobName()->willReturn('import_job');

        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $step1->getName()->willReturn('step_1');
        $job->getSteps()->willReturn([$step1]);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('step_1');

        $jobRegistry->get('import_job')->willReturn($job);

        $archivistFilesystem->writeStream(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->archive($stepExecution);
    }

    public function it_does_not_archive_a_file_when_reader_is_invalid(
        FilesystemOperator $archivistFilesystem,
        JobRegistry $jobRegistry,
        ItemReaderInterface $reader,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step1,
    ): void {
        $jobInstance->getJobName()->willReturn('import_job');

        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $step1->getName()->willReturn('step_1');
        $step1->getReader()->willReturn($reader);
        $job->getSteps()->willReturn([$step1]);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('step_1');

        $jobRegistry->get('import_job')->willReturn($job);

        $archivistFilesystem->writeStream(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->archive($stepExecution);
    }

    public function it_skips_past_steps(
        FilesystemOperator $localFilesystem,
        FilesystemOperator $archivistFilesystem,
        JobRegistry $jobRegistry,
        FileReaderInterface $reader,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step1,
        ItemStep $step2,
        JobParameters $jobParameters,
    ): void {
        $jobInstance->getJobName()->willReturn('import_job');
        $jobInstance->getType()->willReturn('import');

        $jobParameters->has('storage')->willReturn(true);
        $jobParameters->get('storage')->willReturn(['type' => 'local', 'file_path' => '/tmp/import.xlsx']);

        $jobExecution->getId()->willReturn(1);
        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('step_2');

        $step1->getName()->willReturn('step_1');
        $step1->getReader()->shouldNotBeCalled();
        $step2->getName()->willReturn('step_2');
        $step2->getReader()->willReturn($reader);
        $job->getSteps()->willReturn([$step1, $step2]);

        $jobRegistry->get('import_job')->willReturn($job);

        $expectedStream = fopen('php://memory', 'r');
        $localFilesystem->fileExists('/tmp/import.xlsx')->willReturn(true);
        $localFilesystem->readStream('/tmp/import.xlsx')->willReturn($expectedStream);

        $expectedFilePath = 'import/import_job/1/input/import.xlsx';
        $archivistFilesystem->writeStream($expectedFilePath, $expectedStream)->shouldBeCalledOnce();

        $this->archive($stepExecution);
    }

    public function it_supports_step_execution_when_the_step_is_an_item_step_with_usable_reader(
        JobRegistry $jobRegistry,
        FileReaderInterface $reader,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step1,
    ): void {
        $jobInstance->getJobName()->willReturn('import_job');

        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $step1->getName()->willReturn('step_1');
        $step1->getReader()->willReturn($reader);
        $job->getSteps()->willReturn([$step1]);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('step_1');

        $jobRegistry->get('import_job')->willReturn($job);

        $this->supports($stepExecution)->shouldReturn(true);
    }

    public function it_does_not_support_step_execution_when_the_step_is_not_an_item_step(
        JobRegistry $jobRegistry,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        TaskletStep $step1,
    ): void {
        $jobInstance->getJobName()->willReturn('import_job');

        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $step1->getName()->willReturn('step_1');

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('step_1');

        $jobRegistry->get('import_job')->willReturn($job);

        $this->supports($stepExecution)->shouldReturn(false);
    }

    public function it_does_not_support_step_execution_when_the_step_is_an_item_step_with_not_usable_reader(
        JobRegistry $jobRegistry,
        ItemReaderInterface $reader,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step1,
    ): void {
        $jobInstance->getJobName()->willReturn('import_job');

        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $step1->getName()->willReturn('step_1');
        $step1->getReader()->willReturn($reader);
        $job->getSteps()->willReturn([$step1]);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('step_1');

        $jobRegistry->get('import_job')->willReturn($job);

        $this->supports($stepExecution)->shouldReturn(false);
    }

    public function it_does_not_support_step_execution_when_the_job_does_not_exists(
        JobRegistry $jobRegistry,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
    ): void {
        $jobInstance->getJobName()->willReturn('import_job');

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $jobRegistry->get('import_job')->willThrow(\Exception::class);

        $this->supports($stepExecution)->shouldReturn(false);
    }

    public function it_does_not_support_step_execution_when_the_job_implementation_is_not_valid(
        JobRegistry $jobRegistry,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        JobInterface $job,
    ): void {
        $jobInstance->getJobName()->willReturn('import_job');

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $jobRegistry->get('import_job')->willReturn($job);

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
        $jobInstance->getJobName()->willReturn('import_job');

        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $job->getSteps()->willReturn([]);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('step_1');

        $jobRegistry->get('import_job')->willReturn($job);

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
        $jobInstance->getJobName()->willReturn('import_job');

        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $step1->getName()->willReturn('a_step');
        $step2->getName()->willReturn('a_step');
        $job->getSteps()->willReturn([$step1, $step2]);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getStepName()->willReturn('a_step');

        $jobRegistry->get('import_job')->willReturn($job);

        $this->supports($stepExecution)->shouldReturn(false);
    }
}
