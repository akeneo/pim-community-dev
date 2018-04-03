<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\FamilyVariantRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\FamilyVariantRepositoryInterface;
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
