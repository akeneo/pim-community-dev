<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;

class VariantGroupFieldSetterSpec extends ObjectBehavior
{
    function let(
        GroupRepositoryInterface $groupRepository
    ) {
        $this->beConstructedWith(
            $groupRepository,
            ['variant_group']
        );
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\SetterInterface');
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\FieldSetterInterface');
    }

    function it_supports_categories_field()
    {
        $this->supportsField('variant_group')->shouldReturn(true);
        $this->supportsField('groups')->shouldReturn(false);
    }

    function it_checks_valid_data_format(ProductInterface $product)
    {
        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'variant_group',
                'Pim\Component\Catalog\Updater\Setter\VariantGroupFieldSetter',
                ['not a string']
            )
        )->during('setFieldData', [$product, 'variant_group', ['not a string']]);
    }

    function it_sets_variant_group_field(
        $groupRepository,
        ProductInterface $product,
        GroupInterface $shirt,
        GroupInterface $cross,
        GroupInterface $up,
        GroupTypeInterface $variantType,
        GroupTypeInterface $nonVariantType
    ) {
        $groupRepository->findOneByIdentifier('shirt')->willReturn($shirt);
        $shirt->getType()->willReturn($variantType);
        $variantType->isVariant()->willReturn(true);

        $product->getGroups()->willReturn([$up, $cross]);

        $up->getType()->willReturn($nonVariantType);
        $nonVariantType->isVariant()->willReturn(false);

        $cross->getType()->willReturn($variantType);
        $variantType->isVariant()->willReturn(true);
        $product->removeGroup($cross)->shouldBeCalled();

        $product->addGroup($shirt)->shouldBeCalled();

        $this->setFieldData($product, 'variant_group', 'shirt');
    }

    function it_fails_if_the_group_code_is_not_variant(
        $groupRepository,
        ProductInterface $product,
        GroupInterface $pack,
        GroupTypeInterface $nonVariantType
    ) {
        $groupRepository->findOneByIdentifier('pack')->willReturn($pack);
        $pack->getType()->willReturn($nonVariantType);
        $nonVariantType->isVariant()->willReturn(false);

        $this->shouldThrow(
            InvalidPropertyException::validGroupExpected(
                'variant_group',
                'Cannot process group, only variant groups are supported',
                'Pim\Component\Catalog\Updater\Setter\VariantGroupFieldSetter',
                'pack'
            )
        )->during('setFieldData', [$product, 'variant_group', 'pack']);
    }

    function it_fails_if_the_group_code_is_not_found(
        $groupRepository,
        ProductInterface $product,
        GroupInterface $pack,
        GroupTypeInterface $nonVariantType
    ) {
        $groupRepository->findOneByIdentifier('not valid code')->willReturn(null);
        $pack->getType()->willReturn($nonVariantType);
        $nonVariantType->isVariant()->willReturn(false);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'variant_group',
                'variant group code',
                'The variant group does not exist',
                'Pim\Component\Catalog\Updater\Setter\VariantGroupFieldSetter',
                'not valid code'
            )
        )->during('setFieldData', [$product, 'variant_group', 'not valid code']);
    }
}
