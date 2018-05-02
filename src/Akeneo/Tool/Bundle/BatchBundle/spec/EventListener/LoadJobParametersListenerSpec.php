<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class LoadJobParametersListenerSpec extends ObjectBehavior
{
    function let(JobParametersFactory $jobParametersFactory)
    {
        $this->beConstructedWith($jobParametersFactory);
    }

    function it_sets_job_parameters_into_job_execution(
        $jobParametersFactory,
        JobExecution $jobExecution,
        LifecycleEventArgs $event,
        JobParameters $jobParameters
    ) {
        $jobParametersFactory->createFromRawParameters($jobExecution)->willReturn($jobParameters);
        $jobExecution->setJobParameters($jobParameters)->shouldBeCalled();
        $this->postLoad($jobExecution, $event);
    }
}
