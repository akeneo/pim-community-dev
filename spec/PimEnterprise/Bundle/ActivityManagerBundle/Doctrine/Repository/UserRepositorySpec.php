<?php

namespace spec\Akeneo\ActivityManager\Bundle\Doctrine\Repository;

use Akeneo\ActivityManager\Bundle\Doctrine\Repository\UserRepository;
use Akeneo\ActivityManager\Component\Repository\UserRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

class UserRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, ClassMetadata $classMetadata)
    {
        $entityManager->getClassMetadata()->willReturn($classMetadata);

        $this->beConstructedWith($entityManager, 'user');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserRepository::class);
    }

    function it_is_a_project_repository()
    {
        $this->shouldImplement(UserRepositoryInterface::class);
    }

    function it_is_a_doctrine_repository()
    {
        $this->shouldImplement(ObjectRepository::class);
        $this->shouldHaveType('Doctrine\ORM\EntityRepository');
    }
}
