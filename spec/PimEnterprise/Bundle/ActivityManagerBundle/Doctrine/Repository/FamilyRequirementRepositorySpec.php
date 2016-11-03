<?php

namespace spec\Akeneo\ActivityManager\Bundle\Doctrine\Repository;

use Akeneo\ActivityManager\Bundle\Doctrine\Repository\FamilyRequirementRepository;
use Akeneo\ActivityManager\Component\Repository\FamilyRequirementRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FamilyRequirementRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, ClassMetadata $classMetadata)
    {
        $entityManager->getClassMetadata('AttributeRequirement')->willReturn($classMetadata);

        $this->beConstructedWith($entityManager, 'AttributeRequirement');
    }

    function it_is_family_attribute_requirement()
    {
        $this->shouldImplement(FamilyRequirementRepositoryInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyRequirementRepository::class);
    }
}
