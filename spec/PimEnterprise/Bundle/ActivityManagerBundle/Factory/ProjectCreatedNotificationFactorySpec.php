<?php

namespace spec\Akeneo\ActivityManager\Bundle\Factory;

use PhpSpec\ObjectBehavior;

class ProjectCreatedNotificationFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Pim\Bundle\NotificationBundle\Entity\Notification');
    }

    function it_returns_factory() {
        $parameters['due_date'] = '2019-12-23';
        $parameters['project_label'] = 'The prject label';
        $parameters['filters'] = 'filters';

        $this->create($parameters)->shouldReturnAnInstanceOf('Pim\Bundle\NotificationBundle\Entity\Notification');
    }
}
