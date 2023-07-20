<?php

namespace spec\Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Archiver\FileReaderArchiver;
use Akeneo\Tool\Component\Connector\Reader\File\Csv\Reader as CsvReader;
use League\Flysystem\FilesystemOperator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileReaderArchiverSpec extends ObjectBehavior
{
    function let(
        FilesystemOperator $filesystem,
        JobRegistry $jobRegistry,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $this->beConstructedWith($filesystem, $jobRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FileReaderArchiver::class);
    }

    function it_create_a_file_when_reader_is_valid(
        FilesystemOperator $filesystem,
        JobRegistry $jobRegistry,
        CsvReader $reader,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step,
        JobParameters $jobParameters
    ) {
        $pathname = tempnam(sys_get_temp_dir(), 'spec');
        $filename = basename($pathname);

        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobRegistry->get('my_job_name')->willReturn($job);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $job->getSteps()->willReturn([$step]);
        $step->getReader()->willReturn($reader);

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('storage')->willReturn(true);
        $jobParameters->get('storage')->willReturn(['type' => 'local', 'file_path' => $pathname]);

        $filesystem->writeStream(
            'type' . DIRECTORY_SEPARATOR .
                'my_job_name' . DIRECTORY_SEPARATOR .
                '12' . DIRECTORY_SEPARATOR .
                'input' . DIRECTORY_SEPARATOR .
                $filename,
            Argument::any()
        )->shouldBeCalled();

        $this->archive($stepExecution);

        unlink($pathname);
    }

    function it_doesnt_create_a_file_when_writer_is_invalid(
        FilesystemOperator $filesystem,
        JobRegistry $jobRegistry,
        ItemReaderInterface $reader,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step
    ) {
        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobRegistry->get('my_job_name')->willReturn($job);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $job->getSteps()->willReturn([$step]);
        $step->getReader()->willReturn($reader);

        $filesystem->writeStream(Argument::any())->shouldNotBeCalled();

        $this->archive($stepExecution);
    }

    function it_returns_the_name_of_the_archiver()
    {
        $this->getName()->shouldReturn('input');
    }

    function it_doesnt_create_a_file_if_step_is_not_an_item_step(
        FilesystemOperator $filesystem,
        JobRegistry $jobRegistry,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        AbstractStep $step
    ) {
        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobRegistry->get('my_job_name')->willReturn($job);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $job->getSteps()->willReturn([$step]);

        $filesystem->writeStream(Argument::any())->shouldNotBeCalled();

        $this->archive($stepExecution);
    }

    function it_returns_true_for_the_supported_job(
        JobRegistry $jobRegistry,
        CsvReader $reader,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step
    ) {
        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobRegistry->get('my_job_name')->willReturn($job);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $job->getSteps()->willReturn([$step]);
        $step->getReader()->willReturn($reader);

        $this->supports($stepExecution)->shouldReturn(true);
    }

    function it_returns_false_for_the_unsupported_job(
        JobRegistry $jobRegistry,
        ItemReaderInterface $reader,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step
    ) {
        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobRegistry->get('my_job_name')->willReturn($job);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $job->getSteps()->willReturn([$step]);
        $step->getReader()->willReturn($reader);

        $this->supports($stepExecution)->shouldReturn(false);
    }
}
