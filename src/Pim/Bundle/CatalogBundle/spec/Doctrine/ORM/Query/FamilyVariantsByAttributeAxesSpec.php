<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\FamilyVariantsByAttributeAxes;
use Pim\Component\Catalog\FamilyVariant\Query\FamilyVariantsByAttributeAxesInterface;

class FamilyVariantsByAttributeAxesSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantsByAttributeAxes::class);
    }

    function it_it_is_a_query()
    {
        $this->shouldImplement(FamilyVariantsByAttributeAxesInterface::class);
    }
}
