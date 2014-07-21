<?php

namespace spec\Pim\Bundle\TransformBundle\Builder;

use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;

class FieldNameBuilderSpec extends ObjectBehavior
{
    const ASSOC_TYPE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\AssociationType';

    function let(SmartManagerRegistry $managerRegistry)
    {
        $this->beConstructedWith($managerRegistry, self::ASSOC_TYPE_CLASS);
    }

    function it_returns_association_type_field_names(
        $managerRegistry,
        ObjectRepository $objectRepository,
        AssociationType $assocType1,
        AssociationType $assocType2
    ) {
        $assocType1->getCode()->willReturn("ASSOC_TYPE_1");
        $assocType2->getCode()->willReturn("ASSOC_TYPE_2");
        $objectRepository->findAll()->willReturn([$assocType1, $assocType2]);
        $managerRegistry->getRepository(self::ASSOC_TYPE_CLASS)->willReturn($objectRepository);

        $this->getAssociationFieldNames()->shouldReturn(
            [
                "ASSOC_TYPE_1-groups",
                "ASSOC_TYPE_1-products",
                "ASSOC_TYPE_2-groups",
                "ASSOC_TYPE_2-products"
            ]
        );
    }
}
