<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Job\Job;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Step\ItemStep;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;
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

    function it_create_a_file_when_writer_is_valid($filesystem, $writer, $jobExecution, $jobInstance, $job, $step) {
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

        $fs->write('tmp', '');

        $filesystem->write("type/alias/12/output/tmp", "", true)->shouldBeCalled();

        $this->archive($jobExecution);
        $fs->delete('tmp');
    }

    function it_doesnt_create_a_file_when_writer_is_invalid(
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
}
