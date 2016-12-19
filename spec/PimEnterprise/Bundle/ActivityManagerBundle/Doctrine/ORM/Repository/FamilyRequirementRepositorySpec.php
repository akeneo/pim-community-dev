<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository\FamilyRequirementRepository;
use PimEnterprise\Component\ActivityManager\Presenter\PresenterInterface;
use PimEnterprise\Component\ActivityManager\Repository\FamilyRequirementRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

class FamilyRequirementRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, PresenterInterface $presenter, ClassMetadata $classMetadata)
    {
        $entityManager->getClassMetadata('AttributeRequirement')->willReturn($classMetadata);

        $this->beConstructedWith($entityManager, $presenter, 'AttributeRequirement');
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
