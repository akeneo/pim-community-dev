<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;

class AttributeGroupManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Manager\AttributeGroupManager');
    }

    function let(
        AttributeGroupRepositoryInterface $repository,
        SaverInterface $groupSaver,
        BulkSaverInterface $attributeSaver
    ) {
        $this->beConstructedWith($repository, $groupSaver, $attributeSaver);
    }

    function it_throws_an_exception_when_removing_an_attribute_group_and_the_default_group_does_not_exist(
        $repository,
        AttributeGroupInterface $group,
        AttributeInterface $attribute
    ) {
        $repository->findDefaultAttributeGroup()->willReturn(null);

        $this->shouldThrow(new \LogicException('The default attribute group should exist.'))
            ->during('removeAttribute', array($group, $attribute));
    }

    function it_add_attributes_to_attribute_group(
        $groupSaver,
        $attributeSaver,
        AttributeGroupInterface $default,
        AttributeGroupInterface $group,
        AttributeInterface $sku,
        AttributeInterface $name
    ) {
        $group->getMaxAttributeSortOrder()->willReturn(5);

        $sku->setSortOrder(6)->shouldBeCalled();
        $group->addAttribute($sku)->shouldBeCalled();

        $name->setSortOrder(7)->shouldBeCalled();
        $group->addAttribute($name)->shouldBeCalled();

        $attributeSaver->saveAll([$sku, $name])->shouldBeCalled();
        $groupSaver->save($group)->shouldBeCalled();

        $this->addAttributes($group, [$sku, $name]);
    }
}
