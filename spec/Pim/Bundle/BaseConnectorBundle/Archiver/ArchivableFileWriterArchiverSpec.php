<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Job\Job;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Step\ItemStep;
use Gaufrette\Filesystem;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Filesystem\ZipFilesystemFactory;
use Pim\Bundle\BaseConnectorBundle\Writer\File\CsvWriter;
use Prophecy\Argument;

class ArchivableFileWriterArchiverSpec extends ObjectBehavior
{
    function let(
        ZipFilesystemFactory $factory,
        Filesystem $filesystem
    ) {
        $this->beConstructedWith($factory, '/root', $filesystem);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Archiver\ArchivableFileWriterArchiver');
    }

    function it_doesnt_create_a_file_when_writer_is_invalid(
        CsvWriter $writer,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step,
        $filesystem
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getJob()->willReturn($job);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getWriter()->willReturn($writer);
        $writer->getWrittenFiles()->willReturn([]);
        $writer->getPath()->willReturn('/tmp/tmp');

        $filesystem->write(Argument::any())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }
}
