<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository\AttributeRepository;
use PimEnterprise\Component\ActivityManager\Repository\AttributeRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

class AttributeRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, ClassMetadata $classMetadata)
    {
        $entityManager->getClassMetadata('Attribute')->willReturn($classMetadata);

        $this->beConstructedWith($entityManager, 'Attribute');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeRepository::class);
    }

    function it_is_a_doctrine_repository()
    {
        $this->shouldHaveType(EntityRepository::class);
    }

    function it_is_an_attribute_repository()
    {
        $this->shouldImplement(AttributeRepositoryInterface::class);
    }
}
