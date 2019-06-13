<?php

namespace spec\Akeneo\Tool\Component\Connector;

use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Connector\LogKey;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LogArchiverSpec extends ObjectBehavior
{
    function let(Filesystem $filesystem)
    {
        $this->beConstructedWith($filesystem);
    }

    function it_sends_log_to_flysystem($filesystem)
    {
        $importInstance = new JobInstance(null, JobInstance::TYPE_IMPORT, 'csv_import');
        $importExecution = (new JobExecution())
            ->setJobInstance($importInstance)
            ->setLogFile(__FILE__)
        ;

        $event = new JobExecutionEvent($importExecution);
        $filesystem->writeStream(Argument::cetera())->shouldBeCalled();

        $this->archive($event);
    }
}
