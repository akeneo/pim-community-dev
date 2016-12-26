<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Notification;

use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectCreatedNotificationFactory;
use PhpSpec\ObjectBehavior;

class ProjectCreatedNotificationFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Pim\Bundle\NotificationBundle\Entity\Notification');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectCreatedNotificationFactory::class);
    }

    function it_creates_a_notification()
    {
        $parameters['due_date'] = '2019-12-23';
        $parameters['project_label'] = 'The project label';
        $parameters['filters'] = 'filters';

        $this->create($parameters)->shouldReturnAnInstanceOf('Pim\Bundle\NotificationBundle\Entity\Notification');
    }
}
