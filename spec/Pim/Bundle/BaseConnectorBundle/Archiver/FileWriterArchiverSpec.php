<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Job\Job;
use Akeneo\Bundle\BatchBundle\Step\AbstractStep;
use Akeneo\Bundle\BatchBundle\Step\ItemStep;
use Gaufrette\Adapter\Local as LocalAdapter;
use Gaufrette\Filesystem;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Writer\File\CsvWriter;
use Prophecy\Argument;

class FileWriterArchiverSpec extends ObjectBehavior
{
    function let(
        Filesystem $filesystem,
        CsvWriter $writer,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step
    ) {
        $this->beConstructedWith($filesystem);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Archiver\FileWriterArchiver');
    }

    function it_creates_a_file_when_writer_is_valid($filesystem, $writer, $jobExecution, $jobInstance, $job, $step)
    {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getWriter()->willReturn($writer);
        $writer->getWrittenFiles()->willReturn([]);
        $writer->getPath()->willReturn('/tmp/tmp');

        $adapter = new LocalAdapter('/tmp');
        $fs = new Filesystem($adapter);

        $fs->write('tmp', '', true);

        $filesystem->write("type/alias/12/output/tmp", "", true)->shouldBeCalled();

        $this->archive($jobExecution);
    }

    function it_doesnt_create_a_file_when_written_files_is_greater_than_two(
        $filesystem,
        $writer,
        $jobExecution,
        $jobInstance,
        $job,
        $step
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getWriter()->willReturn($writer);
        $writer->getWrittenFiles()->willReturn(['path_one', 'path_two']);
        $writer->getPath()->willReturn('/tmp/tmp');

        $filesystem->write(Argument::any())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_doesnt_create_a_file_when_writer_is_invalid(
        $filesystem,
        ItemWriterInterface $writer,
        $jobExecution,
        $jobInstance,
        $job,
        $step
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getWriter()->willReturn($writer);
        $writer->getWrittenFiles()->willReturn(['path_one']);
        $writer->getPath()->willReturn('/tmp/invalidwriter');

        $filesystem->write(Argument::any())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_returns_the_name_of_the_archiver()
    {
        $this->getName()->shouldReturn('output');
    }

    function it_doesnt_create_a_file_if_step_is_not_an_item_step(
        $filesystem,
        $jobExecution,
        $jobInstance,
        $job,
        AbstractStep $step
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);

        $filesystem->write(Argument::any())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_returns_true_for_the_supported_job(
        $writer,
        $jobExecution,
        $jobInstance,
        $job,
        $step
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getWriter()->willReturn($writer);
        $writer->getWrittenFiles()->willReturn(['path_one']);
        $writer->getPath()->willReturn('/tmp/tmp');

        $adapter = new LocalAdapter('/tmp');
        $fs = new Filesystem($adapter);

        $fs->write('tmp', '', true);

        $this->supports($jobExecution)->shouldReturn(true);
    }

    function it_returns_false_for_the_unsupported_job(
        ItemWriterInterface $writer,
        $jobExecution,
        $jobInstance,
        $job,
        $step
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getWriter()->willReturn($writer);
        $writer->getWrittenFiles()->willReturn(['path_one']);
        $writer->getPath()->willReturn('/tmp/unsupported_job_file_writer_archiver');

        $this->supports($jobExecution)->shouldReturn(false);
    }
}
