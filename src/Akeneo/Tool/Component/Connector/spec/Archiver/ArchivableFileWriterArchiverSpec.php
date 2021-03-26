<?php

namespace spec\Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Archiver\ArchivableFileWriterArchiver;
use Akeneo\Tool\Component\Connector\Archiver\ZipFilesystemFactory;
use Akeneo\Tool\Component\Connector\Writer\File\Csv\Writer;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ArchivableFileWriterArchiverSpec extends ObjectBehavior
{
    function let(
        ZipFilesystemFactory $zipFactory,
        Filesystem $zipFilesystem,
        FilesystemInterface $archivistFilesystem,
        ZipArchiveAdapter $zipAdapter,
        FilesystemProvider $filesystemProvider,
        JobRegistry $jobRegistry
    ) {
        $zipFilesystem->getAdapter()->willReturn($zipAdapter);
        $zipFactory->createZip(Argument::any())->willReturn($zipFilesystem);
        $this->beConstructedWith($zipFactory, $archivistFilesystem, $jobRegistry, $filesystemProvider);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ArchivableFileWriterArchiver::class);
    }

    function it_doesnt_create_a_file_when_writer_is_invalid(
        Filesystem $zipFilesystem,
        JobRegistry $jobRegistry,
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
        JobRegistry $jobRegistry,
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
        JobRegistry $jobRegistry,
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
        Filesystem $zipFilesystem,
        JobRegistry $jobRegistry,
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
        JobRegistry $jobRegistry,
        ZipArchiveAdapter $zipAdapter,
        FilesystemProvider $filesystemProvider,
        Filesystem $zipFilesystem,
        FilesystemInterface $archivistFilesystem,
        FilesystemInterface $catalogStorageFilesystem,
        Writer $writer,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step,
        \ZipArchive $zipArchive
    ) {
        $file1 = tempnam(sys_get_temp_dir(), 'spec');
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
        $writer->getWrittenFiles()->willReturn([
            WrittenFileInfo::fromLocalFile($file1, 'file1'),
            WrittenFileInfo::fromFileStorage('a/b/c/media.png', 'catalogStorage', 'media.png')
        ]);

        $writer->getPath()->willReturn($zipFile);

        $archivistFilesystem->has('type/my_job_name/12/archive')->willReturn(false);
        $archivistFilesystem->createDir('type/my_job_name/12/archive')->shouldBeCalled();

        $filesystemProvider->getFilesystem('catalogStorage')->shouldBeCalled()->willReturn($catalogStorageFilesystem);
        $remoteStream = \tmpfile();
        $catalogStorageFilesystem->readStream('a/b/c/media.png')->shouldBeCalled()->willReturn($remoteStream);

        $zipFilesystem->putStream('file1', Argument::type('resource'))->shouldBeCalled();
        $zipFilesystem->putStream('media.png', $remoteStream)->shouldBeCalled();

        $zipAdapter->getArchive()->willReturn($zipArchive);

        $archivistFilesystem->writeStream('type/my_job_name/12/archive/'.basename($zipFile), Argument::any())->shouldBeCalled();

        $this->archive($jobExecution);

        unlink($file1);
        if (\is_resource($remoteStream)) {
            \fclose($remoteStream);
        }
    }
}
