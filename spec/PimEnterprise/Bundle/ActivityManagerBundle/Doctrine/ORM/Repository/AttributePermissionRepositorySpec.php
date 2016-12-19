<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository\AttributePermissionRepository;
use PimEnterprise\Component\ActivityManager\Repository\AttributePermissionRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

class AttributePermissionRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, ClassMetadata $classMetadata)
    {
        $entityManager->getClassMetadata('AttributeGroupAccesses')->willReturn($classMetadata);

        $this->beConstructedWith($entityManager, 'AttributeGroupAccesses');
    }

    function it_is_attribute_permission_requirement()
    {
        $this->shouldImplement(AttributePermissionRepositoryInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributePermissionRepository::class);
    }
}
