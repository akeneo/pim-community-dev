<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository\PreProcessingRepository;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;

class PreProcessingRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, ClassMetadata $classMetadata)
    {
        $entityManager->getClassMetadata('Product')->willReturn($classMetadata);

        $this->beConstructedWith($entityManager, 'akeneo_activity_manager_completeness_per_attribute_group');
    }

    function it_is_structured_attribute_repository()
    {
        $this->shouldImplement(PreProcessingRepositoryInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PreProcessingRepository::class);
    }
}
