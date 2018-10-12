<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\FamilyVariantRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Prophecy\Argument;

class FamilyVariantRepositorySpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager, ClassMetadata $classMetadata)
    {
        $this->beConstructedWith($entityManager, $classMetadata);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantRepository::class);
    }

    function it_is_a_family_variant_repository()
    {
        $this->shouldImplement(FamilyVariantRepositoryInterface::class);
    }
}
