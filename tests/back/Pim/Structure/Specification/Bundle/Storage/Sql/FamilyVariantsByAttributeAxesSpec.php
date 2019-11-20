<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\Storage\Sql;

use Akeneo\Pim\Structure\Bundle\Storage\Sql\FamilyVariantsByAttributeAxes;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
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
