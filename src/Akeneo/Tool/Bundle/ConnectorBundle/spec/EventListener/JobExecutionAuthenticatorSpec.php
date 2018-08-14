<?php

namespace spec\Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\JobExecutionAuthenticator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class JobExecutionAuthenticatorSpec extends ObjectBehavior
{
    function let(UserProviderInterface $userProvider, TokenStorageInterface $tokenStorage)
    {
        $this->beConstructedWith($userProvider, $tokenStorage);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(JobExecutionAuthenticator::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldHaveType(EventSubscriberInterface::class);
    }

    function it_returns_subscribed_events()
    {
        $this->getSubscribedEvents()->shouldReturn([EventInterface::BEFORE_JOB_EXECUTION => 'authenticate']);
    }

    function it_authenticates_user_with_token(
        $userProvider,
        $tokenStorage,
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        UserInterface $user
    ) {
        $event->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobExecution->getUser()->willReturn('julia');

        $jobParameters->has('is_user_authenticated')->willReturn(true);
        $jobParameters->get('is_user_authenticated')->willReturn(true);

        $userProvider->loadUserByUsername('julia')->willReturn($user);

        $user->getRoles()->willReturn(['role']);

        $token  = new UsernamePasswordToken($user->getWrappedObject(), null, 'main', ['role']);
        $tokenStorage->setToken($token)->shouldBeCalled();

        $this->authenticate($event);
    }

    function it_does_not_authenticate_user_when_user_is_null(
        $tokenStorage,
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        UserInterface $user
    ) {
        $event->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobExecution->getUser()->willReturn(null);

        $token  = new UsernamePasswordToken($user->getWrappedObject(), null, 'main', ['role']);
        $tokenStorage->setToken($token)->shouldNotBeCalled();

        $this->authenticate($event);
    }

    function it_does_authenticates_user_when_no_job_parameters(
        $tokenStorage,
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        UserInterface $user
    ) {
        $event->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobParameters()->willReturn(null);
        $jobExecution->getUser()->willReturn('julia');

        $token  = new UsernamePasswordToken($user->getWrappedObject(), null, 'main', ['role']);
        $tokenStorage->setToken($token)->shouldNotBeCalled();

        $this->authenticate($event);
    }

    function it_does_not_authenticates_user_when_it_is_not_configured_in_job_parameters(
        $tokenStorage,
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        UserInterface $user
    ) {
        $event->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobExecution->getUser()->willReturn('julia');

        $jobParameters->has('is_user_authenticated')->willReturn(false);

        $token  = new UsernamePasswordToken($user->getWrappedObject(), null, 'main', ['role']);
        $tokenStorage->setToken($token)->shouldNotBeCalled();

        $this->authenticate($event);
    }

    function it_does_not_authenticates_user_when_it_is_not_activated_in_job_parameters(
        $tokenStorage,
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        UserInterface $user
    ) {
        $event->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobExecution->getUser()->willReturn('julia');

        $jobParameters->has('is_user_authenticated')->willReturn(false);
        $jobParameters->get('is_user_authenticated')->willReturn(false);

        $token  = new UsernamePasswordToken($user->getWrappedObject(), null, 'main', ['role']);
        $tokenStorage->setToken($token)->shouldNotBeCalled();

        $this->authenticate($event);
    }

    function it_throws_exception_if_username_is_not_found(
        $userProvider,
        $tokenStorage,
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        JobParameters $jobParameters,
        UserInterface $user
    ) {
        $event->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobExecution->getUser()->willReturn('julia');

        $jobParameters->has('is_user_authenticated')->willReturn(true);
        $jobParameters->get('is_user_authenticated')->willReturn(true);

        $userProvider->loadUserByUsername('julia')->willThrow(UsernameNotFoundException::class);

        $token  = new UsernamePasswordToken($user->getWrappedObject(), null, 'main', ['role']);
        $tokenStorage->setToken($token)->shouldNotBeCalled();

        $this->shouldThrow(UsernameNotFoundException::class)->during('authenticate', [$event]);
    }
}
