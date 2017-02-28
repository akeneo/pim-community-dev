<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\EventListener;

use PimEnterprise\Bundle\TeamworkAssistantBundle\EventListener\EventTranslationSubscriber;
use PimEnterprise\Component\TeamworkAssistant\Event\ProjectEvent;
use PimEnterprise\Component\TeamworkAssistant\Event\ProjectEvents;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class EventTranslationSubscriberSpec extends ObjectBehavior
{
    function let(
        EventDispatcherInterface $eventDispatcher,
        IdentifiableObjectRepositoryInterface $projectRepository
    ) {
        $this->beConstructedWith($eventDispatcher, $projectRepository, 'project_calculation');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EventTranslationSubscriber::class);
    }

    function it_is_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            EventInterface::AFTER_JOB_EXECUTION => 'projectCalculated',
        ]);
    }

    function it_dispatches_an_event_when_the_project_is_calculated(
        $eventDispatcher,
        $projectRepository,
        JobExecutionEvent $jobExecutionEvent,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ProjectInterface $project
    ) {
        $jobExecutionEvent->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('project_code')->willReturn('code');
        $jobInstance->getCode()->willReturn('project_calculation');

        $projectRepository->findOneByIdentifier('code')->willReturn($project);

        $eventDispatcher->dispatch(
            ProjectEvents::PROJECT_CALCULATED,
            Argument::type(ProjectEvent::class)
        )->shouldBeCalled();

        $this->projectCalculated($jobExecutionEvent)->shouldReturn(null);
    }

    function it_only_dispatches_events_if_a_project_calculation_job_is_done(
        $eventDispatcher,
        $projectRepository,
        JobExecutionEvent $jobExecutionEvent,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ProjectInterface $project
    ) {
        $jobExecutionEvent->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getCode()->willReturn('another_job');

        $projectRepository->findOneByIdentifier('another_job')->willReturn($project);

        $eventDispatcher->dispatch(Argument::cetera())->shouldNotBeCalled();

        $this->projectCalculated($jobExecutionEvent)->shouldReturn(null);
    }

    function it_does_not_dispatch_event_when_a_calculation_is_done_if_the_project_does_not_exist(
        $eventDispatcher,
        $projectRepository,
        JobExecutionEvent $jobExecutionEvent,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $jobExecutionEvent->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('project_code')->willReturn('code');
        $jobInstance->getCode()->willReturn('project_calculation');

        $projectRepository->findOneByIdentifier('code')->willReturn(null);

        $eventDispatcher->dispatch(Argument::cetera())->shouldNotBeCalled();

        $this->projectCalculated($jobExecutionEvent)->shouldReturn(null);
    }
}
