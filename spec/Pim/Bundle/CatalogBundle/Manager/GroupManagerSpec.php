<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\CatalogEvents;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupTypeRepository;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

class GroupManagerSpec extends ObjectBehavior
{
    const ATTRIBUTE_CLASS  = 'Pim\Bundle\CatalogBundle\Entity\Attribute';
    const GROUP_CLASS      = 'Pim\Bundle\CatalogBundle\Entity\Group';
    const GROUP_TYPE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\GroupType';
    const PRODUCT_CLASS    = 'Pim\Bundle\CatalogBundle\Model\Product';

    function let(
        RegistryInterface $registry,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            $registry,
            $eventDispatcher,
            self::GROUP_CLASS,
            self::GROUP_TYPE_CLASS,
            self::PRODUCT_CLASS,
            self::ATTRIBUTE_CLASS
        );
    }

    function it_should_dispatch_an_event_when_remove_a_group(
        $eventDispatcher,
        $registry,
        ObjectManager $objectManager,
        Group $group
    ) {
        $eventDispatcher->dispatch(
            CatalogEvents::PRE_REMOVE_GROUP,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $registry->getManager()->willReturn($objectManager);
        $objectManager->remove($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->remove($group);
    }

    function it_should_find_available_axis(
        $registry,
        AttributeRepository $attRepository,
        AbstractAttribute $attribute1,
        AbstractAttribute $attribute2
    ) {
        $registry->getRepository(self::ATTRIBUTE_CLASS)->willReturn($attRepository);
        $attRepository->findAllAxis()->willReturn([$attribute1, $attribute2]);

        $this->getAvailableAxis()->shouldReturn([$attribute1, $attribute2]);
    }

    function it_should_returns_available_axis_as_a_sorted_choice(
        $registry,
        AttributeRepository $attRepository,
        AbstractAttribute $attribute1,
        AbstractAttribute $attribute2
    ) {
        $attribute1->getId()->willReturn(1);
        $attribute1->getLabel()->willReturn('Foo');

        $attribute2->getId()->willReturn(2);
        $attribute2->getLabel()->willReturn('Bar');

        $registry->getRepository(self::ATTRIBUTE_CLASS)->willReturn($attRepository);
        $attRepository->findAllAxis()->willReturn([$attribute1, $attribute2]);

        $this->getAvailableAxisChoices()->shouldReturn([2 => 'Bar', 1 => 'Foo']);
    }

    function it_should_returns_a_group_repository($registry, GroupRepository $groupRepository)
    {
        $registry->getRepository(self::GROUP_CLASS)->willReturn($groupRepository);

        $this->getRepository()->shouldReturn($groupRepository);
    }

    function it_should_returns_a_group_type_repository(
        $registry,
        GroupTypeRepository $groupTypeRepository
    ) {
        $registry->getRepository(self::GROUP_TYPE_CLASS)->willReturn($groupTypeRepository);

        $this->getGroupTypeRepository()->shouldReturn($groupTypeRepository);
    }
}
