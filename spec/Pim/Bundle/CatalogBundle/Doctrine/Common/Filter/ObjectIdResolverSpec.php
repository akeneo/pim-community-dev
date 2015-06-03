<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Filter;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;

class ObjectIdResolverSpec extends ObjectBehavior
{
    function let(ManagerRegistry $managerRegistry)
    {
        $this->beConstructedWith($managerRegistry);
        $this->addFieldMapping('family', 'familyClass');
    }

    function it_gets_ids_from_code($managerRegistry, ObjectManager $manager, ObjectRepository $repository, FamilyInterface $camcorders, FamilyInterface $shirt, FamilyInterface $men)
    {
        $managerRegistry->getManagerForClass('familyClass')->willReturn($manager);
        $manager->getRepository('familyClass')->willReturn($repository);

        $repository->findOneBy(['code' => 'camcorders'])->willReturn($camcorders);
        $repository->findOneBy(['code' => 'shirt'])->willReturn($shirt);
        $repository->findOneBy(['code' => 'men'])->willReturn($men);

        $camcorders->getId()->willReturn(2);
        $shirt->getId()->willReturn(12);
        $men->getId()->willReturn(32);

        $this->getIdsFromCodes('family', ['camcorders', 'shirt', 'men'])->shouldReturn([2, 12, 32]);
    }

    function it_throws_an_exception_if_the_call_mapping_is_not_well_set()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('getIdsFromCodes', ['group', ['mug']]);
    }

    function it_throws_an_exception_if_one_of_the_elements_is_not_found($managerRegistry, ObjectManager $manager, ObjectRepository $repository, FamilyInterface $camcorders)
    {
        $managerRegistry->getManagerForClass('familyClass')->willReturn($manager);
        $manager->getRepository('familyClass')->willReturn($repository);

        $repository->findOneBy(['code' => 'camcorders'])->willReturn($camcorders);
        $repository->findOneBy(['code' => 'shirt'])->willReturn(null);

        $camcorders->getId()->willReturn(2);

        $this->shouldThrow('\Pim\Bundle\CatalogBundle\Exception\ObjectNotFoundException')->during('getIdsFromCodes', ['family', ['camcorders', 'shirt', 'men']]);
    }
}
