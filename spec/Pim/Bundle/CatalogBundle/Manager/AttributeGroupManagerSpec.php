<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeGroupRepository;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Prophecy\Argument;

class AttributeGroupManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Manager\AttributeGroupManager');
    }

    function let(ObjectManager $objectManager, AttributeGroupRepository $repository)
    {
        $this->beConstructedWith($objectManager, $repository);
    }

    function it_throws_an_exception_when_removing_an_attribute_group_and_the_default_group_does_not_exist(
        $repository,
        AttributeGroup $group,
        AbstractAttribute $attribute
    ) {
        $repository->findDefaultAttributeGroup()->willReturn(null);

        $this->shouldThrow(new \LogicException('The default attribute group should exist.'))
            ->during('removeAttribute', array($group, $attribute));
    }

    function it_removes_an_attribute_group(
        $repository,
        $objectManager,
        AttributeGroup $default,
        AttributeGroup $group,
        AbstractAttribute $attribute
    ) {
        $repository->findDefaultAttributeGroup()->willReturn($default);

        $group->removeAttribute($attribute)->shouldBeCalled();
        $attribute->setGroup($default)->shouldBeCalled();

        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->persist($attribute)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->removeAttribute($group, $attribute);
    }
}
