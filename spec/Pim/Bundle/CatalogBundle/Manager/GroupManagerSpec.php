<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupTypeRepository;
use Pim\Bundle\CatalogBundle\Event\GroupEvents;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Prophecy\Argument;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    function it_is_a_saver()
    {
        $this->shouldHaveType('Pim\Component\Resource\Model\SaverInterface');
    }
    function it_is_a_remover()
    {
        $this->shouldHaveType('Pim\Component\Resource\Model\RemoverInterface');
    }

    function it_throws_exception_when_save_anything_else_than_a_group()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Pim\Bundle\CatalogBundle\Entity\Group", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('save', [$anythingElse]);
    }

    function it_throws_exception_when_remove_anything_else_than_a_group()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Pim\Bundle\CatalogBundle\Entity\Group", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('remove', [$anythingElse]);
    }

    function it_dispatches_an_event_when_removing_a_group(
        $eventDispatcher,
        $registry,
        ObjectManager $objectManager,
        Group $group
    ) {
        $eventDispatcher->dispatch(
            GroupEvents::PRE_REMOVE,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $registry->getManager()->willReturn($objectManager);
        $objectManager->remove($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->remove($group);
    }

    function it_provides_available_axis(
        $registry,
        AttributeRepository $attRepository,
        AbstractAttribute $attribute1,
        AbstractAttribute $attribute2
    ) {
        $registry->getRepository(self::ATTRIBUTE_CLASS)->willReturn($attRepository);
        $attRepository->findAllAxis()->willReturn([$attribute1, $attribute2]);

        $this->getAvailableAxis()->shouldReturn([$attribute1, $attribute2]);
    }

    function it_provides_available_axis_as_a_sorted_choice(
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

    function it_provides_the_group_repository($registry, GroupRepository $groupRepository)
    {
        $registry->getRepository(self::GROUP_CLASS)->willReturn($groupRepository);

        $this->getRepository()->shouldReturn($groupRepository);
    }

    function it_provides_the_group_type_repository(
        $registry,
        GroupTypeRepository $groupTypeRepository
    ) {
        $registry->getRepository(self::GROUP_TYPE_CLASS)->willReturn($groupTypeRepository);

        $this->getGroupTypeRepository()->shouldReturn($groupTypeRepository);
    }
}
