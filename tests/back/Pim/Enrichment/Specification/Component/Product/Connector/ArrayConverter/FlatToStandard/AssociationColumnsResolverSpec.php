<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;

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
                "ASSOC_TYPE_1-product_models",
                "ASSOC_TYPE_2-groups",
                "ASSOC_TYPE_2-products",
                "ASSOC_TYPE_2-product_models",
            ]
        );
    }

    function it_caches_the_associations_even_without_results($assocTypeRepo)
    {
        $assocTypeRepo->findAll()->willReturn([])->shouldBeCalledTimes(1);
        $this->resolveAssociationColumns()->shouldReturn([]);
        $this->resolveAssociationColumns()->shouldReturn([]);
    }
}
