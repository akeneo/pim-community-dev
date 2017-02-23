<?php

namespace spec\PimEnterprise\Bundle\TeamWorkAssistantBundle\Doctrine\ORM\Repository;

use PimEnterprise\Bundle\TeamWorkAssistantBundle\Doctrine\ORM\Repository\FamilyRequirementRepository;
use PimEnterprise\Component\TeamWorkAssistant\Repository\FamilyRequirementRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

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
