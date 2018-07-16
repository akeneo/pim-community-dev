<?php

namespace spec\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository\FamilyRequirementRepository;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\FamilyRequirementRepositoryInterface;

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
