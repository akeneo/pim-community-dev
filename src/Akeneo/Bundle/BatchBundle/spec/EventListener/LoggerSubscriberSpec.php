<?php

namespace spec\Akeneo\Bundle\BatchBundle\EventListener;

use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\Batch\Model\JobExecution;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class LoggerSubscriberSpec extends ObjectBehavior
{
    function let(LoggerInterface $logger, TranslatorInterface $translator)
    {
        $this->beConstructedWith($logger, $translator);
    }

    function it_logs_before_job_execution($logger, JobExecutionEvent $event, JobExecution $jobExecution)
    {
        $event->getJobExecution()->willReturn($jobExecution);
        $jobExecution->__toString()->willReturn('job exec');
        $logger->debug('Job execution starting: job exec')->shouldBeCalled();
        $this->beforeJobExecution($event);
    }
}
