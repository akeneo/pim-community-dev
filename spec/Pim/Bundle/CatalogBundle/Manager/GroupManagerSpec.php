<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\GroupType;

use Pim\Bundle\CatalogBundle\Entity\Group;

use Pim\Bundle\CatalogBundle\Entity\Attribute;

use PhpSpec\ObjectBehavior;

use Doctrine\ORM\EntityManager;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupTypeRepository;

class GroupManagerSpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        AttributeRepository $attributeRepo,
        GroupRepository $groupRepo,
        GroupTypeRepository $groupTypeRepo
    ) {
        $this->beConstructedWith($em, $groupRepo, $groupTypeRepo, $attributeRepo);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Manager\GroupManager');
    }

    function it_provides_available_axis($attributeRepo, Attribute $att1, Attribute $att2)
    {
        $expected = array($att1, $att2);
        $attributeRepo->findAllAxis()->willReturn($expected);
        $this->getAvailableAxis()->shouldReturn($expected);
    }

    function it_provides_available_axis_choices($attributeRepo, Attribute $att1, Attribute $att2)
    {
        $expected = array(5 => 'ALPHA', 2 => 'BETA');

        $att1->getId()->willReturn(2);
        $att1->getLabel()->willReturn('BETA');

        $att2->getId()->willReturn(5);
        $att2->getLabel()->willReturn('ALPHA');

        $attributeRepo->findAllAxis()->willReturn(array($att1, $att2));

        $this->getAvailableAxisChoices()->shouldReturn($expected);
    }

    function it_provides_choices_list($groupRepo)
    {
        $groupRepo->getChoices()->willReturn(array(2 => 'Foo', 5 => 'Baz', 3 => 'Bar'));
        $this->getChoices()->shouldReturn(array(3 => 'Bar', 5 => 'Baz', 2 => 'Foo'));
    }

    function it_provides_group_type_choices_list(
        $groupTypeRepo,
        GroupType $groupType1,
        GroupType $groupType2,
        GroupType $groupType3,
        GroupType $groupType4
    ) {
        $groupType1->getId()->willReturn(1);
        $groupType1->getLabel()->willReturn('Foo');
        $groupType2->getId()->willReturn(2);
        $groupType2->getLabel()->willReturn('Qux');
        $groupType3->getId()->willReturn(3);
        $groupType3->getLabel()->willReturn('Baz');
        $groupType4->getId()->willReturn(4);
        $groupType4->getLabel()->willReturn('Bar');

        $groupTypeRepo->findBy(array('variant' => true))->willReturn(array($groupType1, $groupType4));
        $groupTypeRepo->findBy(array('variant' => false))->willReturn(array($groupType2, $groupType3));

        $this->getTypeChoices(true)->shouldReturn(array(4 => 'Bar', 1 => 'Foo'));
        $this->getTypeChoices(false)->shouldReturn(array(3 => 'Baz', 2 => 'Qux'));
    }

    function it_provides_group_repository($groupRepo)
    {
        $this->getRepository()->shouldReturn($groupRepo);
    }

    function it_provides_group_type_repository($groupTypeRepo)
    {
        $this->getGroupTypeRepository()->shouldReturn($groupTypeRepo);
    }
}
