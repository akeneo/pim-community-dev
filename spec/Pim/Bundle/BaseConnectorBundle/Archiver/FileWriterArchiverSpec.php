<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Step\AbstractStep;
use Akeneo\Component\Batch\Step\ItemStep;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Writer\File\CsvWriter;
use Prophecy\Argument;

class FileWriterArchiverSpec extends ObjectBehavior
{
    function let(Filesystem $filesystem)
    {
        $this->beConstructedWith($filesystem);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Archiver\FileWriterArchiver');
    }

    function it_creates_a_file_when_writer_is_valid(
        $filesystem,
        CsvWriter $writer,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step
    ) {
        $pathname = tempnam(sys_get_temp_dir(), 'spec');
        $filename = basename($pathname);

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getWriter()->willReturn($writer);
        $writer->getWrittenFiles()->willReturn([]);
        $writer->getPath()->willReturn($pathname);

        $filesystem->put(
            'type' . DIRECTORY_SEPARATOR .
            'alias' . DIRECTORY_SEPARATOR .
            '12' . DIRECTORY_SEPARATOR .
            'output' . DIRECTORY_SEPARATOR .
            $filename,
            ''
        )->shouldBeCalled();

        $this->archive($jobExecution);

        unlink($pathname);
    }

//    function it_creates_a_file_even_when_written_files_is_greater_than_two(
//        $filesystem,
//        CsvWriter $writer,
//        JobExecution $jobExecution,
//        JobInstance $jobInstance,
//        Job $job,
//        ItemStep $step
//    ) {
//        $jobExecution->getJobInstance()->willReturn($jobInstance);
//        $jobExecution->getId()->willReturn(12);
//        $jobInstance->getJob()->willReturn($job);
//        $jobInstance->getType()->willReturn('type');
//        $jobInstance->getAlias()->willReturn('alias');
//        $job->getSteps()->willReturn([$step]);
//        $step->getWriter()->willReturn($writer);
//
//        $pathname1 = tempnam(sys_get_temp_dir(), 'spec1');
//        $filename1 = basename($pathname1);
//        $pathname2 = tempnam(sys_get_temp_dir(), 'spec2');
//        $filename2 = basename($pathname2);
//        $pathname3 = tempnam(sys_get_temp_dir(), 'spec3');
//        $writer->getWrittenFiles()->willReturn(
//            [
//                $pathname1 => $filename1,
//                $pathname2 => $filename2
//            ]
//        );
//        $writer->getPath()->willReturn($pathname3);
//
//        $filesystem->put(Argument::cetera())->shouldBeCalled();
//
//        $this->archive($jobExecution);
//
//        unlink($pathname1);
//        unlink($pathname2);
//        unlink($pathname3);
//    }

    function it_doesnt_create_a_file_when_writer_is_invalid(
        $filesystem,
        ItemWriterInterface $writer,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
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
        $filesystem,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        AbstractStep $step
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);

        $filesystem->put(Argument::cetera())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_supports_a_compatible_job(
        CsvWriter $writer,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step
    ) {
        $pathname = tempnam(sys_get_temp_dir(), 'spec');

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getWriter()->willReturn($writer);
        $writer->getWrittenFiles()->willReturn(['path_one']);
        $writer->getPath()->willReturn($pathname);

        $this->supports($jobExecution)->shouldReturn(true);

        unlink($pathname);
    }

    function it_does_not_support_a_incompatible_job(
        ItemWriterInterface $writer,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getWriter()->willReturn($writer);

        $this->supports($jobExecution)->shouldReturn(false);
    }
}
