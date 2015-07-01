<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat\Product;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;

class AssociationColumnsResolverSpec extends ObjectBehavior
{
    function let(AssociationTypeRepositoryInterface $assocTypeRepo)
    {
        $this->beConstructedWith($assocTypeRepo);
    }

    function it_resolves_association_type_field_names(
        $assocTypeRepo,
        AssociationTypeInterface $assocType1,
        AssociationTypeInterface $assocType2
    ) {
        $assocType1->getCode()->willReturn("ASSOC_TYPE_1");
        $assocType2->getCode()->willReturn("ASSOC_TYPE_2");
        $assocTypeRepo->findAll()->willReturn([$assocType1, $assocType2]);
        $this->resolveAssociationColumns()->shouldReturn(
            [
                "ASSOC_TYPE_1-groups",
                "ASSOC_TYPE_1-products",
                "ASSOC_TYPE_2-groups",
                "ASSOC_TYPE_2-products"
            ]
        );
    }
}
