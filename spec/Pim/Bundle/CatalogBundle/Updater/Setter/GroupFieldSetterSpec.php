<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

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
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Setter\SetterInterface');
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Setter\FieldSetterInterface');
    }

    function it_supports_groups_field() {
        $this->supportsField('groups')->shouldReturn(true);
        $this->supportsField('categories')->shouldReturn(false);
    }

    function it_checks_valid_data_format(ProductInterface $product)
    {
        $this->shouldThrow(
            InvalidArgumentException::arrayExpected(
                'groups',
                'setter',
                'groups',
                'string'
            )
        )->during('setFieldData', [$product, 'groups', 'not an array']);

        $this->shouldThrow(
            InvalidArgumentException::arrayStringValueExpected(
                'groups',
                0,
                'setter',
                'groups',
                'array'
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
        $nonVariantType->isVariant()->willReturn(false);
        $product->removeGroup($up)->shouldBeCalled();

        $pack->getType()->willReturn($nonVariantType);
        $nonVariantType->isVariant()->willReturn(false);

        $cross->getType()->willReturn($nonVariantType);
        $nonVariantType->isVariant()->willReturn(false);

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
        $nonVariantType->isVariant()->willReturn(false);
        $groupRepository->findOneByIdentifier('not valid code')->willReturn(null);

        $this->shouldThrow(
            InvalidArgumentException::expected(
                'groups',
                'existing group code',
                'setter',
                'groups',
                'not valid code'
            )
        )->during('setFieldData', [$product, 'groups', ['pack', 'not valid code']]);
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
            InvalidArgumentException::expected(
                'groups',
                'non variant group code',
                'setter',
                'groups',
                'variant'
            )
        )->during('setFieldData', [$product, 'groups', ['pack', 'variant']]);
    }
}
