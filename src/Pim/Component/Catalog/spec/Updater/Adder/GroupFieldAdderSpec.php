<?php

namespace spec\Pim\Component\Catalog\Updater\Adder;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;

class GroupFieldAdderSpec extends ObjectBehavior
{
    function let(
        GroupRepositoryInterface $groupRepository
    ) {
        $this->beConstructedWith(
            $groupRepository,
            ['groups']
        );
    }

    function it_is_a_adder()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Adder\AdderInterface');
        $this->shouldImplement('Pim\Component\Catalog\Updater\Adder\FieldAdderInterface');
    }

    function it_supports_categories_field()
    {
        $this->supportsField('groups')->shouldReturn(true);
        $this->supportsField('categories')->shouldReturn(false);
    }

    function it_checks_valid_data_format(ProductInterface $product)
    {
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'groups',
                'Pim\Component\Catalog\Updater\Adder\GroupFieldAdder',
                'not an array'
            )
        )->during('addFieldData', [$product, 'groups', 'not an array']);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'groups',
                'one of the group codes is not a string, "array" given',
                'Pim\Component\Catalog\Updater\Adder\GroupFieldAdder',
                [['array of array']]
            )
        )->during('addFieldData', [$product, 'groups', [['array of array']]]);
    }

    function it_adds_groups_field(
        $groupRepository,
        ProductInterface $product,
        GroupInterface $pack,
        GroupInterface $cross,
        GroupInterface $up,
        GroupTypeInterface $nonVariantType
    ) {
        $groupRepository->findOneByIdentifier('pack')->willReturn($pack);
        $groupRepository->findOneByIdentifier('cross')->willReturn($cross);

        $pack->getType()->willReturn($nonVariantType);
        $nonVariantType->isVariant()->willReturn(false);

        $cross->getType()->willReturn($nonVariantType);
        $nonVariantType->isVariant()->willReturn(false);

        $product->addGroup($pack)->shouldBeCalled();
        $product->addGroup($cross)->shouldBeCalled();

        $this->addFieldData($product, 'groups', ['pack', 'cross']);
    }

    function it_fails_if_the_group_code_does_not_exist(
        $groupRepository,
        ProductInterface $product,
        GroupInterface $pack,
        GroupTypeInterface $nonVariantType
    ) {
        $groupRepository->findOneByIdentifier('pack')->willReturn($pack);
        $pack->getType()->willReturn($nonVariantType);
        $nonVariantType->isVariant()->willReturn(false);
        $groupRepository->findOneByIdentifier('not valid code')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'groups',
                'group code',
                'The group does not exist',
                'Pim\Component\Catalog\Updater\Adder\GroupFieldAdder',
                'not valid code'
            )
        )->during('addFieldData', [$product, 'groups', ['pack', 'not valid code']]);
    }

    function it_fails_if_the_group_code_does_not_correspond_to_a_simple_group(
        $groupRepository,
        ProductInterface $product,
        GroupInterface $pack,
        GroupInterface $variant,
        GroupTypeInterface $nonVariantType,
        GroupTypeInterface $variantType
    ) {
        $groupRepository->findOneByIdentifier('pack')->willReturn($pack);
        $pack->getType()->willReturn($nonVariantType);
        $nonVariantType->isVariant()->willReturn(false);
        $groupRepository->findOneByIdentifier('variant')->willReturn($variant);
        $variant->getType()->willReturn($variantType);
        $variantType->isVariant()->willReturn(true);

        $this->shouldThrow(
            InvalidPropertyException::validGroupExpected(
                'groups',
                'Cannot process variant group, only groups are supported',
                'Pim\Component\Catalog\Updater\Adder\GroupFieldAdder',
                'variant'
            )
        )->during('addFieldData', [$product, 'groups', ['pack', 'variant']]);
    }
}
