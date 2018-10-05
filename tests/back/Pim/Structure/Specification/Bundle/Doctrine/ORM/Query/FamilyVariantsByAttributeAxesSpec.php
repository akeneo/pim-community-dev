<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Query\FamilyVariantsByAttributeAxes;
use Akeneo\Pim\Structure\Component\FamilyVariant\Query\FamilyVariantsByAttributeAxesInterface;

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
