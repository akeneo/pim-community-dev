<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\GroupFieldSetter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;

class GroupFieldSetterSpec extends ObjectBehavior
{
    function let(
        GroupRepositoryInterface $groupRepository
    ) {
        $this->beConstructedWith(
            $groupRepository,
            ['groups']
        );
    }

    function it_is_a_setter()
    {
        $this->shouldImplement(SetterInterface::class);
        $this->shouldImplement(FieldSetterInterface::class);
    }

    function it_supports_groups_field()
    {
        $this->supportsField('groups')->shouldReturn(true);
        $this->supportsField('categories')->shouldReturn(false);
    }

    function it_checks_valid_data_format(ProductInterface $product)
    {
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'groups',
                GroupFieldSetter::class,
                'not an array'
            )
        )->during('setFieldData', [$product, 'groups', 'not an array']);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'groups',
                'one of the group codes is not a string, "array" given',
                GroupFieldSetter::class,
                [['array of array']]
            )
        )->during('setFieldData', [$product, 'groups', [['array of array']]]);
    }

    function it_sets_groups_field(
        $groupRepository,
        ProductInterface $product,
        GroupInterface $pack,
        GroupInterface $cross,
        GroupInterface $up,
        GroupTypeInterface $nonVariantType
    ) {
        $groupRepository->findOneByIdentifier('pack')->willReturn($pack);
        $groupRepository->findOneByIdentifier('cross')->willReturn($cross);

        $product->getGroups()->willReturn([$up]);

        $up->getType()->willReturn($nonVariantType);
        $product->removeGroup($up)->shouldBeCalled();
        $pack->getType()->willReturn($nonVariantType);
        $cross->getType()->willReturn($nonVariantType);

        $product->addGroup($pack)->shouldBeCalled();
        $product->addGroup($cross)->shouldBeCalled();

        $this->setFieldData($product, 'groups', ['pack', 'cross']);
    }

    function it_fails_if_the_group_code_does_not_exist(
        $groupRepository,
        ProductInterface $product,
        GroupInterface $pack,
        GroupTypeInterface $nonVariantType
    ) {
        $groupRepository->findOneByIdentifier('pack')->willReturn($pack);
        $pack->getType()->willReturn($nonVariantType);
        $groupRepository->findOneByIdentifier('not valid code')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'groups',
                'group code',
                'The group does not exist',
                GroupFieldSetter::class,
                'not valid code'
            )
        )->during('setFieldData', [$product, 'groups', ['pack', 'not valid code']]);
    }
}
