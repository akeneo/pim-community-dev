<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository\UserRepository;
use PimEnterprise\Component\ActivityManager\Repository\UserRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
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

    function it_is_a_user_repository()
    {
        $this->shouldImplement(UserRepositoryInterface::class);
    }

    function it_is_a_searchable_repository()
    {
        $this->shouldImplement(SearchableRepositoryInterface::class);
    }

    function it_is_a_doctrine_repository()
    {
        $this->shouldHaveType(EntityRepository::class);
    }
}
