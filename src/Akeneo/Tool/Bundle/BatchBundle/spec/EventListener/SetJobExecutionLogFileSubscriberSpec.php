<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PhpSpec\ObjectBehavior;

class SetJobExecutionLogFileSubscriberSpec extends ObjectBehavior
{
    function let(BatchLogHandler $handler)
    {
        $this->beConstructedWith($handler);
    }

    function it_sets_job_execution_log_file($handler, JobExecutionEvent $event, JobExecution $execution)
    {
        $handler->getFilename()->willReturn('myfilename');
        $event->getJobExecution()->willReturn($execution);
        $execution->setLogFile('myfilename')->shouldBeCalled();
        $this->setJobExecutionLogFile($event);
    }
}
