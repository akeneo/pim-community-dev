<?php

namespace spec\Pim\Component\Connector\Archiver;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Step\AbstractStep;
use Akeneo\Component\Batch\Step\ItemStep;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Archiver\ZipFilesystemFactory;
use Pim\Component\Connector\Writer\File\Csv\Writer;
use Prophecy\Argument;

class ArchivableFileWriterArchiverSpec extends ObjectBehavior
{
    function let(
        ZipFilesystemFactory $factory,
        Filesystem $filesystem,
        LocalAdapter $adapter,
        JobRegistry $jobRegistry
    ) {
        $filesystem->getAdapter()->willReturn($adapter);
        $adapter->getPathPrefix()->willReturn(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'archivist');
        $this->beConstructedWith($factory, $filesystem, $jobRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Archiver\ArchivableFileWriterArchiver');
    }

    function it_doesnt_create_a_file_when_writer_is_invalid(
        $filesystem,
        $jobRegistry,
        Writer $writer,
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
        $jobInstance->getJobName()->willReturn('my_job_name');
        $job->getSteps()->willReturn([$step]);
        $step->getWriter()->willReturn($writer);
        $writer->getWrittenFiles()->willReturn([]);
        $writer->getPath()->willReturn(sys_get_temp_dir() . DIRECTORY_SEPARATOR  . 'file.csv');

        $filesystem->put(Argument::any())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_returns_the_name_of_the_archiver()
    {
        $this->getName()->shouldReturn('archive');
    }

    function it_returns_true_for_the_supported_job(
        $jobRegistry,
        Writer $writer,
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
        $writer->getWrittenFiles()->willReturn(['path_one', 'path_two']);
        $writer->getPath()->willReturn(sys_get_temp_dir() . DIRECTORY_SEPARATOR  . 'file.csv');

        $this->supports($jobExecution)->shouldReturn(true);
    }

    function it_returns_false_for_the_unsupported_job(
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

    function it_doesnt_create_a_file_if_step_is_not_an_item_step(
        $filesystem,
        $jobRegistry,
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

        $filesystem->put(Argument::any())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_creates_a_file_if_writer_is_correct(
        $jobRegistry,
        Writer $writer,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step,
        $factory,
        $filesystem
    ) {
        $file1 = tempnam(sys_get_temp_dir(), 'spec');
        $file2 = tempnam(sys_get_temp_dir(), 'spec');

        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobRegistry->get('my_job_name')->willReturn($job);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $job->getSteps()->willReturn([$step]);
        $step->getWriter()->willReturn($writer);
        $writer->getWrittenFiles()->willReturn([$file1 => 'file1', $file2 => 'file2']);
        $writer->getPath()->willReturn(sys_get_temp_dir());
        $filesystem->has('type/my_job_name/12/archive')->willReturn(false);
        $filesystem->createDir('type/my_job_name/12/archive')->shouldBeCalled();

        $factory->createZip(Argument::any())->willReturn($filesystem);
        $filesystem->put('file1', '')->shouldBeCalled();
        $filesystem->put('file2', '')->shouldBeCalled();

        $this->archive($jobExecution);

        unlink($file1);
        unlink($file2);
    }
}
