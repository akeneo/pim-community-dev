<?php

namespace spec\Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Connector\Archiver\ArchivableFileWriterArchiver;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\Archiver\ZipFilesystemFactory;
use Akeneo\Tool\Component\Connector\Writer\File\Csv\Writer;
use Prophecy\Argument;

class ArchivableFileWriterArchiverSpec extends ObjectBehavior
{
    function let(
        ZipFilesystemFactory $zipFactory,
        Filesystem $zipFilesystem,
        Filesystem $archivistFilesystem,
        ZipArchiveAdapter $zipAdapter,
        JobRegistry $jobRegistry
    ) {
        $zipFilesystem->getAdapter()->willReturn($zipAdapter);
        $zipFactory->createZip(Argument::any())->willReturn($zipFilesystem);
        $this->beConstructedWith($zipFactory, $archivistFilesystem, $jobRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ArchivableFileWriterArchiver::class);
    }

    function it_doesnt_create_a_file_when_writer_is_invalid(
        $zipFilesystem,
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

        $zipFilesystem->put(Argument::any())->shouldNotBeCalled();

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
        $zipFilesystem,
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

        $zipFilesystem->put(Argument::any())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_creates_a_file_if_writer_is_correct(
        $jobRegistry,
        $zipAdapter,
        $zipFactory,
        $zipFilesystem,
        $archivistFilesystem,
        Writer $writer,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step,
        \ZipArchive $zipArchive
    ) {
        $file1 = tempnam(sys_get_temp_dir(), 'spec');
        $file2 = tempnam(sys_get_temp_dir(), 'spec');
        $zipFile = tempnam(sys_get_temp_dir(), 'spec');
        rename($zipFile, $zipFile.'.zip');
        $zipFile = $zipFile.'.zip';

        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobRegistry->get('my_job_name')->willReturn($job);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');

        $executionContext = new \Akeneo\Tool\Component\Batch\Item\ExecutionContext();
        $executionContext->put('working_directory', sys_get_temp_dir());

        $jobExecution->getExecutionContext()->willReturn($executionContext);

        $job->getSteps()->willReturn([$step]);
        $step->getWriter()->willReturn($writer);
        $writer->getWrittenFiles()->willReturn([$file1 => 'file1', $file2 => 'file2']);

        $writer->getPath()->willReturn($zipFile);

        $archivistFilesystem->has('type/my_job_name/12/archive')->willReturn(false);
        $archivistFilesystem->createDir('type/my_job_name/12/archive')->shouldBeCalled();

        $zipFilesystem->putStream('file1', Argument::type('resource'))->shouldBeCalled();
        $zipFilesystem->putStream('file2', Argument::type('resource'))->shouldBeCalled();

        $zipAdapter->getArchive()->willReturn($zipArchive);

        $archivistFilesystem->writeStream('type/my_job_name/12/archive/'.basename($zipFile), Argument::any())->shouldBeCalled();

        $this->archive($jobExecution);

        unlink($file1);
        unlink($file2);
    }
}
