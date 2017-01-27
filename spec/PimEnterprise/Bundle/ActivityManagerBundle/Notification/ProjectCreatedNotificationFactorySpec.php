<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Notification;

use Akeneo\Component\Localization\Presenter\DatePresenter;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectCreatedNotificationFactory;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

class ProjectCreatedNotificationFactorySpec extends ObjectBehavior
{
    function let(DatePresenter $datePresenter)
    {
        $this->beConstructedWith($datePresenter, 'Pim\Bundle\NotificationBundle\Entity\Notification');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectCreatedNotificationFactory::class);
    }

    function it_creates_a_notification(
        $datePresenter,
        UserInterface $user,
        ProjectInterface $project,
        LocaleInterface $locale
    ) {
        $user->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');
        $project->getDueDate()->willReturn('01/07/2030');
        $project->getLabel()->willReturn('The project label');
        $project->getCode()->willReturn('the-project-label-en-US-mobile');

        $datePresenter->present(
            '01/07/2030',
            ['locale' => 'en_US']
        )->willReturn('07/01/2030');

        $this->create($project, $user)->shouldReturnAnInstanceOf('Pim\Bundle\NotificationBundle\Entity\Notification');
    }
}
