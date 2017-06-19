<?php

namespace spec\Pim\Component\Connector\Archiver;

use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Step\AbstractStep;
use Akeneo\Component\Batch\Step\ItemStep;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Reader\File\Csv\Reader as CsvReader;
use Prophecy\Argument;

class FileReaderArchiverSpec extends ObjectBehavior
{
    function let(
        Filesystem $filesystem,
        JobRegistry $jobRegistry
    ) {
        $this->beConstructedWith($filesystem, $jobRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Archiver\FileReaderArchiver');
    }

    function it_create_a_file_when_reader_is_valid(
        $jobRegistry,
        CsvReader $reader,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step,
        JobParameters $jobParameters,
        $filesystem
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
        $jobParameters->get('filePath')->willReturn($pathname);

        $filesystem->put(
            'type' . DIRECTORY_SEPARATOR .
            'my_job_name' . DIRECTORY_SEPARATOR .
            '12' . DIRECTORY_SEPARATOR .
            'input' . DIRECTORY_SEPARATOR .
            $filename,
            ''
        )->shouldBeCalled();

        $this->archive($jobExecution);

        unlink($pathname);
    }

    function it_doesnt_create_a_file_when_writer_is_invalid(
        $jobRegistry,
        ItemReaderInterface $reader,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step,
        $filesystem
    ) {
        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobRegistry->get('my_job_name')->willReturn($job);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $job->getSteps()->willReturn([$step]);
        $step->getReader()->willReturn($reader);

        $filesystem->put(Argument::any())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_returns_the_name_of_the_archiver()
    {
        $this->getName()->shouldReturn('input');
    }

    function it_doesnt_create_a_file_if_step_is_not_an_item_step(
        $jobRegistry,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        AbstractStep $step,
        $filesystem
    ) {
        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobRegistry->get('my_job_name')->willReturn($job);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $job->getSteps()->willReturn([$step]);

        $filesystem->put(Argument::any())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_returns_true_for_the_supported_job(
        $jobRegistry,
        CsvReader $reader,
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

        $this->supports($jobExecution)->shouldReturn(true);
    }

    function it_returns_false_for_the_unsupported_job(
        $jobRegistry,
        ItemReaderInterface $reader,
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

        $this->supports($jobExecution)->shouldReturn(false);
    }
}
