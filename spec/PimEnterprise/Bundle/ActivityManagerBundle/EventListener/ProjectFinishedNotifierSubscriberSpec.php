<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\EventListener;

use PimEnterprise\Bundle\ActivityManagerBundle\EventListener\ProjectFinishedNotifierSubscriber;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectFinishedNotificationFactory;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvent;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvents;
use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectCompletenessRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\UserRepositoryInterface;
use Akeneo\Component\Localization\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectFinishedNotifierSubscriberSpec extends ObjectBehavior
{
    function let(
        ProjectFinishedNotificationFactory $factory,
        NotifierInterface $notifier,
        UserRepositoryInterface $userRepository,
        PresenterInterface $datePresenter,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository
    ) {
        $this->beConstructedWith($factory, $notifier, $userRepository, $datePresenter, $projectCompletenessRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectFinishedNotifierSubscriber::class);
    }

    function it_is_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            ProjectEvents::PROJECT_CALCULATED => 'projectFinished',
        ]);
    }

    function it_notifies_users_when_the_project_is_finished(
        $factory,
        $userRepository,
        $notifier,
        $datePresenter,
        $projectCompletenessRepository,
        ProjectEvent $event,
        ProjectInterface $project,
        UserInterface $user,
        UserInterface $owner,
        NotificationInterface $notification,
        LocaleInterface $locale,
        ProjectCompleteness $projectCompleteness
    ) {
        $datetime = new \DateTime('2019-12-23');
        $event->getProject()->willReturn($project);
        $project->getDueDate()->willReturn($datetime);
        $project->getLabel()->willReturn('project label');
        $project->getCode()->willReturn('project-label-en_US-mobile');
        $project->getOwner()->willReturn($owner);

        $datePresenter->present($datetime, ['locale' => 'en_US'])->willReturn('2019-12-23');

        $owner->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');

        $projectCompletenessRepository->getProjectCompleteness($project)->willReturn($projectCompleteness);
        $projectCompleteness->isComplete()->willReturn(true);

        $factory->create(
            'activity_manager.notification.project_finished.owner',
            [
                '%project_label%' => '"project label"',
                '%due_date%' => '"2019-12-23"',
                'project_code' => 'project-label-en_US-mobile'
            ]
        )->willReturn($notification);

        $notifier->notify($notification, [$owner])->shouldBeCalled();


        $userRepository->findContributorsToNotify($project)->willReturn([$user]);
        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn($projectCompleteness);

        $factory->create(
            'activity_manager.notification.project_finished.contributor',
            [
                '%project_label%' => '"project label"',
                'project_code' => 'project-label-en_US-mobile'
            ]
        )->willReturn($notification);

        $notifier->notify($notification, [$user])->shouldBeCalled();

        $this->projectFinished($event)->shouldReturn(null);
    }

    function it_notifies_contributors_when_the_project_is_done_for_them(
        $factory,
        $notifier,
        $userRepository,
        $datePresenter,
        $projectCompletenessRepository,
        ProjectEvent $event,
        ProjectInterface $project,
        UserInterface $user,
        UserInterface $owner,
        NotificationInterface $notification,
        LocaleInterface $locale,
        ProjectCompleteness $projectCompleteness,
        ProjectCompleteness $contributorCompleteness
    ) {
        $datetime = new \DateTime('2019-12-23');
        $event->getProject()->willReturn($project);
        $project->getDueDate()->willReturn($datetime);
        $project->getLabel()->willReturn('project label');
        $project->getCode()->willReturn('project-label-en_US-mobile');
        $project->getOwner()->willReturn($owner);

        $datePresenter->present($datetime, ['locale' => 'en_US'])->willReturn('2019-12-23');

        $owner->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');

        $projectCompletenessRepository->getProjectCompleteness($project)->willReturn($projectCompleteness);
        $projectCompleteness->isComplete()->willReturn(false);

        $factory->create(
            'activity_manager.notification.project_finished.owner',
            [
                '%project_label%' => '"project label"',
                '%due_date%' => '"2019-12-23"',
                'project_code' => 'project-label-en_US-mobile'
            ]
        )->shouldNotBeCalled();

        $userRepository->findContributorsToNotify($project)->willReturn([$user]);
        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn($contributorCompleteness);
        $contributorCompleteness->isComplete()->willReturn(true);

        $factory->create(
            'activity_manager.notification.project_finished.contributor',
            [
                '%project_label%' => '"project label"',
                'project_code' => 'project-label-en_US-mobile'
            ]
        )->willReturn($notification);

        $notifier->notify($notification, [$user])->shouldBeCalled();

        $this->projectFinished($event)->shouldReturn(null);
    }
}
