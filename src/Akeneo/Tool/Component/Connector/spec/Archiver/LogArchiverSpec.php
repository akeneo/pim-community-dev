<?php

namespace spec\Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Connector\Archiver\ArchiverInterface;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LogArchiverSpec extends ObjectBehavior
{
    function let(Filesystem $filesystem)
    {
        $this->beConstructedWith($filesystem);
    }

    function it_is_an_archiver()
    {
        $this->shouldBeAnInstanceOf(ArchiverInterface::class);
    }

    function it_supports_import_jobs()
    {
        $importInstance = new JobInstance(null, JobInstance::TYPE_IMPORT);
        $importExecution = (new JobExecution())->setJobInstance($importInstance);

        $this->supports($importExecution)->shouldReturn(true);
    }

    function it_supports_export_jobs()
    {
        $exportInstance = new JobInstance(null, JobInstance::TYPE_EXPORT);
        $exportExecution = (new JobExecution())->setJobInstance($exportInstance);

        $this->supports($exportExecution)->shouldReturn(true);
    }

    function it_does_not_support_non_import_export_jobs()
    {
        $otherInstance = new JobInstance(null, 'foo');
        $otherExecution = (new JobExecution())->setJobInstance($otherInstance);

        $this->supports($otherExecution)->shouldReturn(false);
    }

    function it_sends_log_to_flysystem($filesystem, JobExecution $jobExecution)
    {
        $jobExecution->getLogFile()->willReturn(__FILE__);
        $filesystem->writeStream(__FILE__, Argument::cetera())->shouldBeCalled();

        $this->archive($jobExecution);
    }
}
