<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\EventListener;

use PimEnterprise\Bundle\ActivityManagerBundle\EventListener\ProjectCreationNotifierSubscriber;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectCreatedNotificationFactory;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvent;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvents;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectCompletenessRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\UserRepositoryInterface;
use Akeneo\Component\Localization\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectCreationNotifierSubscriberSpec extends ObjectBehavior
{
    function let(
        ProjectCreatedNotificationFactory $factory,
        NotifierInterface $notifier,
        UserRepositoryInterface $userRepository,
        PresenterInterface $datePresenter,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository
    ) {
        $this->beConstructedWith($factory, $notifier, $userRepository, $datePresenter, $projectCompletenessRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectCreationNotifierSubscriber::class);
    }

    function it_is_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            ProjectEvents::PROJECT_CALCULATED => 'projectCreated',
        ]);
    }

    function it_notifies_users_when_the_project_is_created(
        $factory,
        $userRepository,
        $notifier,
        $datePresenter,
        $projectCompletenessRepository,
        ProjectEvent $event,
        ProjectInterface $project,
        UserInterface $user,
        NotificationInterface $notification,
        LocaleInterface $locale
    ) {
        $datetime = new \DateTime('2019-12-23');
        $event->getProject()->willReturn($project);
        $project->getDueDate()->willReturn($datetime);
        $project->getLabel()->willReturn('project label');
        $project->getCode()->willReturn('project-label-en_US-mobile');

        $datePresenter->present($datetime, ['locale' => 'en_US'])->willReturn('2019-12-23');

        $user->getId()->willReturn(42);
        $user->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');

        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn(
            [
                'done' => '3',
                'todo' => '7',
                'in_progess' => '5'
            ]
        );

        $factory->create(
            [
                'due_date' => '2019-12-23',
                'project_label' => 'project label',
                'project_code' => 'project-label-en_US-mobile'
            ]
        )->willReturn($notification);

        $userRepository->findContributorsToNotify($project)->willReturn([$user]);
        $notifier->notify($notification, [$user])->shouldBeCalled();

        $this->projectCreated($event)->shouldReturn(null);
    }

    function it_does_not_notify_users_when_the_project_is_created_and_already_to_100_percent_done(
        $notifier,
        $userRepository,
        $datePresenter,
        $projectCompletenessRepository,
        ProjectEvent $event,
        ProjectInterface $project,
        UserInterface $user,
        NotificationInterface $notification,
        LocaleInterface $locale
    ) {
        $datetime = new \DateTime('2019-12-23');
        $event->getProject()->willReturn($project);
        $project->getDueDate()->willReturn($datetime);
        $project->getLabel()->willReturn('project label');
        $project->getCode()->willReturn('project-label-en_US-mobile');

        $datePresenter->present($datetime, ['locale' => 'en_US'])->willReturn('2019-12-23');

        $user->getId()->willReturn(42);
        $user->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');

        $userRepository->findContributorsToNotify($project)->willReturn([$user]);

        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn(
            [
                'done' => '55',
                'todo' => '0',
                'in_progess' => '0'
            ]
        );

        $notifier->notify($notification, [$user])->shouldNotBeCalled();

        $this->projectCreated($event)->shouldReturn(null);
    }
}
