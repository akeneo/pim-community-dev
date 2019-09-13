<?php

namespace spec\Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Connector\Archiver\FileWriterArchiver;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\Writer\File\Csv\Writer;
use Prophecy\Argument;

class FileWriterArchiverSpec extends ObjectBehavior
{
    function let(
        Filesystem $filesystem,
        JobRegistry $jobRegistry
    ) {
        $this->beConstructedWith($filesystem, $jobRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FileWriterArchiver::class);
    }

    function it_creates_a_file_when_writer_is_valid(
        Filesystem $filesystem,
        $jobRegistry,
        Writer $writer,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step
    ) {
        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobRegistry->get('my_job_name')->willReturn($job);

        $pathname = tempnam(sys_get_temp_dir(), 'spec');
        $filename = basename($pathname);

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');

        $job->getSteps()->willReturn([$step]);

        $step->getWriter()->willReturn($writer);

        $writer->getWrittenFiles()->willReturn([$pathname => $filename]);
        $writer->getPath()->willReturn($pathname);

        $filesystem->putStream(
            'type' . DIRECTORY_SEPARATOR .
            'my_job_name' . DIRECTORY_SEPARATOR .
            '12' . DIRECTORY_SEPARATOR .
            'output' . DIRECTORY_SEPARATOR .
            $filename,
            Argument::type('resource')
        )->shouldBeCalled();

        $this->archive($jobExecution);

        unlink($pathname);
    }

    function it_doesnt_create_a_file_when_writer_is_invalid(
        $filesystem,
        $jobRegistry,
        ItemWriterInterface $writer,
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
        $step->getWriter()->willReturn($writer);

        $filesystem->put(Argument::cetera())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_returns_the_name_of_the_archiver()
    {
        $this->getName()->shouldReturn('output');
    }

    function it_doesnt_create_a_file_if_step_is_not_an_item_step(
        $jobRegistry,
        $filesystem,
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

        $filesystem->put(Argument::cetera())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_supports_a_compatible_job(
        $jobRegistry,
        Writer $writer,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step
    ) {
        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobRegistry->get('my_job_name')->willReturn($job);
        $pathname = tempnam(sys_get_temp_dir(), 'spec');
        $filename = basename($pathname);

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $job->getSteps()->willReturn([$step]);
        $step->getWriter()->willReturn($writer);
        $writer->getWrittenFiles()->willReturn([$pathname => $filename]);
        $writer->getPath()->willReturn($pathname);

        $this->supports($jobExecution)->shouldReturn(true);

        unlink($pathname);
    }

    function it_does_not_support_a_incompatible_job(
        $jobRegistry,
        ItemWriterInterface $writer,
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
        $step->getWriter()->willReturn($writer);

        $this->supports($jobExecution)->shouldReturn(false);
    }
}
