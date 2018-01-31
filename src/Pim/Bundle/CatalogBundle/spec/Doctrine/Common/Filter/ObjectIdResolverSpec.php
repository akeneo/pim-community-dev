<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Filter;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\FamilyInterface;

class ObjectIdResolverSpec extends ObjectBehavior
{
    function let(ObjectManager $manager)
    {
        $this->beConstructedWith($manager);
        $this->addFieldMapping('family', 'familyClass');
        $this->addFieldMapping('option', 'optionClass');
    }

    function it_gets_ids_from_codes(
        $manager,
        ObjectRepository $repository,
        FamilyInterface $camcorders,
        FamilyInterface $shirt,
        FamilyInterface $men
    ) {
        $manager->getRepository('familyClass')->willReturn($repository);

        $repository->findOneBy(['code' => 'camcorders'])->willReturn($camcorders);
        $repository->findOneBy(['code' => 'shirt'])->willReturn($shirt);
        $repository->findOneBy(['code' => 'men'])->willReturn($men);

        $camcorders->getId()->willReturn(2);
        $shirt->getId()->willReturn(12);
        $men->getId()->willReturn(32);

        $this->getIdsFromCodes('family', ['camcorders', 'shirt', 'men'])->shouldReturn([2, 12, 32]);
    }

    function it_gets_ids_from_codes_with_attribute(
        $manager,
        ObjectRepository $repository,
        AttributeOptionInterface $purple,
        AttributeInterface $attribute
    ) {
        $manager->getRepository('optionClass')->willReturn($repository);
        $attribute->getId()->willReturn(12);

        $repository->findOneBy(['code' => 'purple', 'attribute' => 12])->willReturn($purple);
        $purple->getId()->willReturn(2);

        $this->getIdsFromCodes('option', ['purple'], $attribute)->shouldReturn([2]);
    }

    function it_throws_an_exception_if_the_call_mapping_is_not_well_set()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('getIdsFromCodes', ['group', ['mug']]);
    }

    function it_throws_an_exception_if_one_of_the_elements_is_not_found(
        $manager,
        ObjectRepository $repository,
        FamilyInterface $camcorders
    ) {
        $manager->getRepository('familyClass')->willReturn($repository);

        $repository->findOneBy(['code' => 'camcorders'])->willReturn($camcorders);
        $repository->findOneBy(['code' => 'shirt'])->willReturn(null);

        $camcorders->getId()->willReturn(2);

        $this->shouldThrow('\Pim\Component\Catalog\Exception\ObjectNotFoundException')->during(
            'getIdsFromCodes',
            ['family', ['camcorders', 'shirt', 'men']]
        );
    }
}
