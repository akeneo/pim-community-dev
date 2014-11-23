<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeGroupRepository;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

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

    function it_is_a_saver()
    {
        $this->shouldImplement('Pim\Component\Resource\Model\SaverInterface');
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldImplement('Pim\Component\Resource\Model\BulkSaverInterface');
    }

    function it_is_a_remover()
    {
        $this->shouldImplement('Pim\Component\Resource\Model\RemoverInterface');
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

    function it_add_attributes_to_attribute_group(
        $objectManager,
        AttributeGroup $default,
        AttributeGroup $group,
        AbstractAttribute $sku,
        AbstractAttribute $name
    ) {
        $group->getMaxAttributeSortOrder()->willReturn(5);

        $sku->setSortOrder(6)->shouldBeCalled();
        $group->addAttribute($sku)->shouldBeCalled();
        $objectManager->persist($sku)->shouldBeCalled();

        $name->setSortOrder(7)->shouldBeCalled();
        $group->addAttribute($name)->shouldBeCalled();
        $objectManager->persist($name)->shouldBeCalled();

        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->addAttributes($group, [$sku, $name]);
    }

    function it_throws_exception_when_save_anything_else_than_a_attribute_group()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a Pim\Bundle\CatalogBundle\Entity\AttributeGroup, "%s" provided',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringSave($anythingElse);
    }

    function it_throws_exception_when_bulk_save_anything_else_than_a_attribute_group(AttributeGroup $group)
    {
        $anythingElse = new \stdClass();
        $mixed = [$group, $anythingElse];
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a Pim\Bundle\CatalogBundle\Entity\AttributeGroup, "%s" provided',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringSaveAll($mixed);
    }

    function it_throws_exception_when_remove_anything_else_than_a_attribute_group()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a Pim\Bundle\CatalogBundle\Entity\AttributeGroup, "%s" provided',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringRemove($anythingElse);
    }
}
