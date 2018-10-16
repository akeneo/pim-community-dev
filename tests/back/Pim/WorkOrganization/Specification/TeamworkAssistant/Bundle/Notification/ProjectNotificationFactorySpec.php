<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Notification;

use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Notification\ProjectNotificationFactory;

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
