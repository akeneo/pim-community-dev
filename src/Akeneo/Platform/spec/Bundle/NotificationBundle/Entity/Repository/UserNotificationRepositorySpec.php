<?php

namespace spec\Akeneo\Platform\Bundle\NotificationBundle\Entity\Repository;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\UserNotification;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

class UserNotificationRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $class)
    {
        $class->name = UserNotification::class;
        $this->beConstructedWith($em, $class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserNotificationRepository::class);
    }

    function it_is_an_entity_repository()
    {
        $this->shouldHaveType('Doctrine\ORM\EntityRepository');
    }
}
