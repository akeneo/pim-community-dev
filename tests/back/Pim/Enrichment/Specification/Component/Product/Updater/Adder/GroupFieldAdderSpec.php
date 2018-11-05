<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\GroupFieldAdder;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\FieldAdderInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;

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
        $this->shouldImplement(AdderInterface::class);
        $this->shouldImplement(FieldAdderInterface::class);
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
                GroupFieldAdder::class,
                'not an array'
            )
        )->during('addFieldData', [$product, 'groups', 'not an array']);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'groups',
                'one of the group codes is not a string, "array" given',
                GroupFieldAdder::class,
                [['array of array']]
            )
        )->during('addFieldData', [$product, 'groups', [['array of array']]]);
    }

    function it_adds_groups_field(
        $groupRepository,
        ProductInterface $product,
        GroupInterface $pack,
        GroupInterface $cross,
        GroupTypeInterface $nonVariantType
    ) {
        $groupRepository->findOneByIdentifier('pack')->willReturn($pack);
        $groupRepository->findOneByIdentifier('cross')->willReturn($cross);

        $pack->getType()->willReturn($nonVariantType);

        $cross->getType()->willReturn($nonVariantType);

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
        $groupRepository->findOneByIdentifier('not valid code')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'groups',
                'group code',
                'The group does not exist',
                GroupFieldAdder::class,
                'not valid code'
            )
        )->during('addFieldData', [$product, 'groups', ['pack', 'not valid code']]);
    }
}
