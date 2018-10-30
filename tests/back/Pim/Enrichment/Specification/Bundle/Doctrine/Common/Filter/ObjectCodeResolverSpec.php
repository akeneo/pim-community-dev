<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;

class ObjectCodeResolverSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager)
    {
        $this->beConstructedWith($objectManager);
        $this->addFieldMapping('family', 'familyClass');
        $this->addFieldMapping('option', 'optionClass');
    }

    function it_gets_codes_from_ids(
        $objectManager,
        ObjectRepository $repository,
        FamilyInterface $camcorders,
        FamilyInterface $shirt,
        FamilyInterface $men
    ) {
        $objectManager->getRepository('familyClass')->willReturn($repository);

        $repository->findOneBy(['id' => 12])->willReturn($camcorders);
        $repository->findOneBy(['id' => 56])->willReturn($shirt);
        $repository->findOneBy(['id' => 123])->willReturn($men);

        $camcorders->getCode()->willReturn('camcorders');
        $shirt->getCode()->willReturn('shirt');
        $men->getCode()->willReturn('men');

        $this->getCodesFromIds('family', [12, 56, 123])->shouldReturn(['camcorders', 'shirt', 'men']);
    }

    function it_gets_codes_from_ids_with_attribute(
        $objectManager,
        ObjectRepository $repository,
        AttributeOptionInterface $purple,
        AttributeInterface $attribute
    ) {
        $objectManager->getRepository('optionClass')->willReturn($repository);
        $attribute->getCode()->willReturn('an_option');

        $repository->findOneBy(['id' => 12])->willReturn($purple);
        $purple->getCode()->willReturn('purple');

        $this->getCodesFromIds('option', [12], $attribute)->shouldReturn(['an_option.purple']);
    }

    function it_throws_an_exception_if_the_call_mapping_is_not_well_set()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('getCodesFromIds', ['group', ['mug']]);
    }

    function it_throws_an_exception_if_one_of_the_elements_is_not_found(
        $objectManager,
        ObjectRepository $repository,
        FamilyInterface $camcorders
    ) {
        $objectManager->getRepository('familyClass')->willReturn($repository);

        $repository->findOneBy(['id' => 23])->willReturn($camcorders);
        $repository->findOneBy(['id' => 56])->willReturn(null);

        $camcorders->getCode()->willReturn('camcorders');

        $this->shouldThrow(ObjectNotFoundException::class)->during(
            'getCodesFromIds',
            ['family', [23, 56, 123]]
        );
    }
}
