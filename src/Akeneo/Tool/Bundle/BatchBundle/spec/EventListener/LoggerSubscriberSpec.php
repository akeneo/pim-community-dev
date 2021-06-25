<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoggerSubscriberSpec extends ObjectBehavior
{
    function let(
        LoggerInterface $logger,
        TranslatorInterface $translator,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $jobInstance->getConnector()->willReturn('my_connector');
        $jobInstance->getJobName()->willReturn('my_jobname');

        $this->beConstructedWith($logger, $translator);
    }

    function it_logs_job_execution_created(
        $logger,
        JobExecutionEvent $event,
        JobExecution $jobExecution
    ) {
        $event->getJobExecution()->willReturn($jobExecution);
        $jobExecution->__toString()->willReturn('job exec');
        $logger->debug(
            'Job execution is created: job exec',
            ["connector" => "my_connector", "jobname" => "my_jobname"]
        )->shouldBeCalled();
        $this->jobExecutionCreated($event);
    }

    function it_logs_before_job_execution(
        $logger,
        JobExecutionEvent $event,
        JobExecution $jobExecution
    ) {
        $event->getJobExecution()->willReturn($jobExecution);
        $jobExecution->__toString()->willReturn('job exec');
        $logger->debug(
            'Job execution starting: job exec',
            ["connector" => "my_connector", "jobname" => "my_jobname"]
        )->shouldBeCalled();
        $this->beforeJobExecution($event);
    }
}
