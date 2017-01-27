<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Notification;

use Akeneo\Component\Localization\Presenter\DatePresenter;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectFinishedNotificationFactory;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

class ProjectFinishedNotificationFactorySpec extends ObjectBehavior
{
    function let(DatePresenter $datePresenter)
    {
        $this->beConstructedWith($datePresenter, 'Pim\Bundle\NotificationBundle\Entity\Notification');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectFinishedNotificationFactory::class);
    }

    function it_creates_a_notification_for_owner(
        $datePresenter,
        UserInterface $owner,
        ProjectInterface $project,
        LocaleInterface $locale
    ) {
        $owner->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');
        $project->getDueDate()->willReturn('01/07/2030');
        $project->getLabel()->willReturn('The project label');
        $project->getCode()->willReturn('the-project-label-en-US-mobile');
        $project->getOwner()->willReturn($owner);

        $datePresenter->present(
            '01/07/2030',
            ['locale' => 'en_US']
        )->willReturn('07/01/2030');

        $this->create($project, 'activity_manager.notification.project_finished.owner')
            ->shouldReturnAnInstanceOf('Pim\Bundle\NotificationBundle\Entity\Notification');
    }

    function it_creates_a_notification_for_contributor(
        $datePresenter,
        UserInterface $owner,
        ProjectInterface $project,
        LocaleInterface $locale
    ) {
        $owner->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');
        $project->getDueDate()->willReturn('01/07/2030');
        $project->getLabel()->willReturn('The project label');
        $project->getCode()->willReturn('the-project-label-en-US-mobile');
        $project->getOwner()->willReturn($owner);

        $datePresenter->present(
            '01/07/2030',
            ['locale' => 'en_US']
        )->willReturn('07/01/2030');

        $this->create($project, 'activity_manager.notification.project_finished.contributor')
            ->shouldReturnAnInstanceOf('Pim\Bundle\NotificationBundle\Entity\Notification');
    }
}
