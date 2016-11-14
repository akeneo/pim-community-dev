<?php

namespace spec\Akeneo\ActivityManager\Bundle\EventListener;

use Akeneo\ActivityManager\Bundle\EventListener\ProjectJobExecutionNotifier;
use Akeneo\ActivityManager\Bundle\Notification\ProjectCreatedNotificationFactory;
use Akeneo\ActivityManager\Component\Event\ProjectEvent;
use Akeneo\ActivityManager\Component\Event\ProjectEvents;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
use Akeneo\ActivityManager\Component\Repository\UserRepositoryInterface;
use Akeneo\Component\Localization\Presenter\PresenterInterface;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectJobExecutionNotifierSpec extends ObjectBehavior
{
    function let(
        ProjectCreatedNotificationFactory $factory,
        NotifierInterface $notifier,
        ProjectRepositoryInterface $projectRepository,
        UserRepositoryInterface $userRepository,
        PresenterInterface $datePresenter
    ) {
        $this->beConstructedWith($factory, $notifier, $projectRepository, $userRepository, $datePresenter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectJobExecutionNotifier::class);
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
        ProjectEvent $event,
        ProjectInterface $project,
        DatagridView $view,
        Group $userGroup,
        UserInterface $user,
        NotificationInterface $notification,
        LocaleInterface $locale
    ) {
        $userGroups = [$userGroup];
        $datetime = new \DateTime('2019-12-23');
        $event->getProject()->willReturn($project);
        $project->getDatagridView()->willReturn($view);
        $view->getFilters()->willReturn('filters');
        $project->getUserGroups()->willReturn($userGroups);
        $project->getOwner()->willReturn($user);
        $project->getDueDate()->willReturn($datetime);
        $project->getLabel()->willReturn('project label');

        $datePresenter->present($datetime, ['locale' => 'en_US'])->willReturn('2019-12-23');

        $user->getId()->willReturn(42);
        $user->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');
        $userGroup->getId()->willReturn(84);

        $factory->create(
            ['due_date' => '2019-12-23', 'project_label' => 'project label', 'filters' => 'filters']
        )->willReturn($notification);

        $userRepository->findContributorToNotify(42, [84])->willReturn([$user]);
        $notifier->notify($notification, [$user])->shouldBeCalled();

        $this->projectCreated($event)->shouldReturn(null);
    }
}
