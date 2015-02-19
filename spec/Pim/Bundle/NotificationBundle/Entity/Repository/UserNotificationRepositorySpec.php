<?php

namespace spec\Pim\Bundle\NotificationBundle\Entity\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

class UserNotificationRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $class)
    {
        $class->name = 'Pim\Bundle\NotificationBundle\Entity\UserNotification';
        $this->beConstructedWith($em, $class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepository');
    }

    function it_is_an_entity_repository()
    {
        $this->shouldHaveType('Doctrine\ORM\EntityRepository');
    }
}
