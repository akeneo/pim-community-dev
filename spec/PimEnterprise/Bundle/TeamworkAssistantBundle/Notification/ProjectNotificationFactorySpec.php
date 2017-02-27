<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Notification;

use Pim\Bundle\NotificationBundle\Entity\Notification;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Notification\ProjectNotificationFactory;

class ProjectNotificationFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(Notification::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectNotificationFactory::class);
    }

    function it_creates_a_notification()
    {
        $routeParams = ['identifier' => 'my-project-code'];
        $parameters = ['%project_label%' => 'Project Label', '%due_date%' => '01/07/2030'];

        $context = [
            'actionType'  => 'project_calculation',
            'buttonLabel' => 'teamwork_assistant.notification.project_calculation.start'
        ];

        $message = 'teamwork_assistant.notification.message';

        $notification = $this->create($routeParams, $parameters, $context, $message);
        $notification->shouldBeAnInstanceOf(Notification::class);
        $notification->getMessage()->shouldReturn('teamwork_assistant.notification.message');
    }
}
