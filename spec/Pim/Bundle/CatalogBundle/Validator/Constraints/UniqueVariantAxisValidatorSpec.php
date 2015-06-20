<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueVariantAxis;
use Prophecy\Argument;
use Symfony\Component\Validator\ExecutionContextInterface;

class UniqueVariantAxisValidatorSpec extends ObjectBehavior
{
    function let(ProductRepositoryInterface $productRepository, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($productRepository);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueVariantAxisValidator');
    }

    function it_validates_product_with_no_groups(
        $context,
        ProductInterface $product,
        UniqueVariantAxis $uniqueVariantAxisConstraint
    ) {
        $product->getGroups()->willReturn(null);
        $context
            ->addViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($product, $uniqueVariantAxisConstraint);
    }

    function it_validates_not_variant_group(
        $context,
        GroupInterface $notVariantGroup,
        GroupTypeInterface $groupTypeInterface,
        UniqueVariantAxis $uniqueVariantAxisConstraint
    ) {
        $notVariantGroup->getType()->willReturn($groupTypeInterface);
        $groupTypeInterface->isVariant()->willReturn(false);
        $context->addViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($notVariantGroup, $uniqueVariantAxisConstraint);
    }

    function it_does_not_add_violation_when_validate_group_with_products_with_unique_combination_of_axis_attributes(
        $context,
        GroupInterface $tShirtVariantGroup,
        GroupTypeInterface $tShirtGroupType,
        ProductInterface $redTShirtProduct,
        ProductInterface $pinkTShirtProduct,
        AttributeInterface $sizeAttribute,
        AttributeInterface $colorAttribute,
        ProductValueInterface $sizeProductValue,
        ProductValueInterface $pinkColorProductValue,
        ProductValueInterface $redColorProductValue,
        UniqueVariantAxis $uniqueVariantAxisConstraint
    ) {
        $tShirtGroupType->isVariant()->willReturn(true);

        $tShirtVariantGroup->getType()->willReturn($tShirtGroupType);
        $tShirtVariantGroup->getProducts()->willReturn([$redTShirtProduct, $pinkTShirtProduct]);
        $tShirtVariantGroup->getAxisAttributes()->willReturn([$sizeAttribute, $colorAttribute]);

        $sizeAttribute->getCode()->willReturn('size');
        $colorAttribute->getCode()->willReturn('color');

        $redTShirtProduct->getValue('size')->willReturn($sizeProductValue);
        $redTShirtProduct->getValue('color')->willReturn($redColorProductValue);

        $sizeProductValue->getOption()->willReturn('XL');
        $redColorProductValue->getOption()->willReturn('Red');

        $pinkTShirtProduct->getValue('size')->willReturn($sizeProductValue);
        $pinkTShirtProduct->getValue('color')->willReturn($pinkColorProductValue);

        $pinkColorProductValue->getOption()->willReturn('Pink');

        $tShirtVariantGroup->getLabel()->willReturn('Groupe TShirt');

        $context
            ->addViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($tShirtVariantGroup, $uniqueVariantAxisConstraint);
    }

    function it_adds_violation_when_validating_group_containing_products_with_non_unique_combination_of_axis_attributes(
        $context,
        GroupInterface $tShirtVariantGroup,
        GroupTypeInterface $tShirtGroupType,
        ProductInterface $redTShirtProduct,
        ProductInterface $redTShirtProduct2,
        AttributeInterface $sizeAttribute,
        AttributeInterface $colorAttribute,
        ProductValueInterface $sizeProductValue,
        ProductValueInterface $colorProductValue,
        UniqueVariantAxis $uniqueVariantAxisConstraint
    ) {
        $tShirtGroupType->isVariant()->willReturn(true);

        $tShirtVariantGroup->getType()->willReturn($tShirtGroupType);
        $tShirtVariantGroup->getProducts()->willReturn([$redTShirtProduct, $redTShirtProduct2]);
        $tShirtVariantGroup->getAxisAttributes()->willReturn([$sizeAttribute, $colorAttribute]);

        $sizeAttribute->getCode()->willReturn('size');
        $colorAttribute->getCode()->willReturn('color');
        $sizeProductValue->getOption()->willReturn('XL');
        $colorProductValue->getOption()->willReturn('Red');

        $redTShirtProduct->getValue('size')->willReturn($sizeProductValue);
        $redTShirtProduct->getValue('color')->willReturn($colorProductValue);

        $redTShirtProduct2->getValue('size')->willReturn($sizeProductValue);
        $redTShirtProduct2->getValue('color')->willReturn($colorProductValue);

        $tShirtVariantGroup->getLabel()->willReturn('Groupe TShirt');

        $context->addViolation(
            'Group "%variant group%" already contains another product with values "%values%"',
            [
                '%variant group%' => 'Groupe TShirt',
                '%values%' => 'size: XL, color: Red',
            ]
        )->shouldBeCalled();

        $this->validate($tShirtVariantGroup, $uniqueVariantAxisConstraint);
    }

    function it_does_not_add_violation_when_product_has_no_groups(
        $context,
        ProductInterface $product,
        UniqueVariantAxis $uniqueVariantAxisConstraint
    ) {
        $product->getGroups()->willReturn(null);

        $context
            ->addViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($product, $uniqueVariantAxisConstraint);
    }

    function it_does_not_add_violation_when_validating_product_in_groups_with_unique_combination_of_axis_attributes(
        $context,
        $productRepository,
        GroupInterface $tShirtVariantGroup,
        GroupTypeInterface $tShirtGroupType,
        GroupInterface $clothesVariantGroup,
        GroupTypeInterface $clothesGroupType,
        ProductInterface $redTShirtProduct,
        AttributeInterface $sizeAttribute,
        AttributeInterface $colorAttribute,
        ProductValueInterface $sizeProductValue,
        ProductValueInterface $redColorProductValue,
        ProductInterface $pinkTShirtProduct,
        ProductValueInterface $pinkColorProductValue,
        UniqueVariantAxis $uniqueVariantAxisConstraint
    ) {
        $tShirtVariantGroup->getId()->willReturn(1);
        $tShirtVariantGroup->getLabel()->willReturn('TShirts');
        $tShirtVariantGroup->getType()->willReturn($tShirtGroupType);
        $tShirtGroupType->isVariant()->willReturn(true);
        $tShirtVariantGroup->getAxisAttributes()->willReturn([$sizeAttribute, $colorAttribute]);

        $clothesVariantGroup->getId()->willReturn(2);
        $clothesVariantGroup->getLabel()->willReturn('Clothes');
        $clothesVariantGroup->getType()->willReturn($clothesGroupType);
        $clothesGroupType->isVariant()->willReturn(true);
        $clothesVariantGroup->getAxisAttributes()->willReturn([$sizeAttribute, $colorAttribute]);

        $sizeAttribute->getCode()->willReturn('size');
        $colorAttribute->getCode()->willReturn('color');

        $redTShirtProduct->getGroups()->willReturn([$tShirtVariantGroup, $clothesVariantGroup]);
        $redTShirtProduct->getId()->willReturn(1);
        $redTShirtProduct->getValue('size')->willReturn($sizeProductValue);
        $redTShirtProduct->getValue('color')->willReturn($redColorProductValue);
        $sizeProductValue->getOption()->willReturn('XL');
        $redColorProductValue->getOption()->willReturn('Red');

        $pinkTShirtProduct->getGroups()->willReturn([$clothesVariantGroup]);
        $pinkTShirtProduct->getId()->willReturn(2);
        $pinkTShirtProduct->getValue('size')->willReturn($sizeProductValue);
        $pinkTShirtProduct->getValue('color')->willReturn($pinkColorProductValue);
        $pinkColorProductValue->getOption()->willReturn('Pink');

        $criteria =
            [
                [
                    'attribute' => $sizeAttribute,
                    'option' => 'XL'
                ],
                [
                    'attribute' => $colorAttribute,
                    'option' => 'Red'
                ]
            ];

        $productRepository->findAllForVariantGroup($tShirtVariantGroup, $criteria)->willReturn([]);
        $productRepository->findAllForVariantGroup($clothesVariantGroup, $criteria)->willReturn([]);

        $context->addViolation()->shouldNotBeCalled(Argument::cetera());

        $this->validate($redTShirtProduct, $uniqueVariantAxisConstraint);
    }

    function it_adds_a_violation_when_validating_product_in_groups_with_non_unique_combination_of_axis_attributes(
        $context,
        $productRepository,
        GroupInterface $tShirtVariantGroup,
        GroupTypeInterface $tShirtGroupType,
        GroupInterface $clothesVariantGroup,
        GroupTypeInterface $clothesGroupType,
        ProductInterface $redTShirtProduct,
        AttributeInterface $sizeAttribute,
        AttributeInterface $colorAttribute,
        ProductValueInterface $sizeProductValue,
        ProductValueInterface $colorProductValue,
        ProductInterface $redTShirtProduct2,
        UniqueVariantAxis $uniqueVariantAxisConstraint
    ) {
        $redTShirtProduct->getGroups()->willReturn([$tShirtVariantGroup, $clothesVariantGroup]);

        $tShirtVariantGroup->getId()->willReturn(1);
        $tShirtVariantGroup->getLabel()->willReturn('TShirts');
        $tShirtVariantGroup->getType()->willReturn($tShirtGroupType);
        $tShirtGroupType->isVariant()->willReturn(true);
        $tShirtVariantGroup->getAxisAttributes()->willReturn([$sizeAttribute, $colorAttribute]);

        $clothesVariantGroup->getId()->willReturn(2);
        $clothesVariantGroup->getLabel()->willReturn('Clothes');
        $clothesVariantGroup->getType()->willReturn($clothesGroupType);
        $clothesGroupType->isVariant()->willReturn(true);
        $clothesVariantGroup->getAxisAttributes()->willReturn([$sizeAttribute, $colorAttribute]);

        $sizeAttribute->getCode()->willReturn('size');
        $colorAttribute->getCode()->willReturn('color');

        $redTShirtProduct->getValue('size')->willReturn($sizeProductValue);
        $redTShirtProduct->getValue('color')->willReturn($colorProductValue);
        $redTShirtProduct->getId()->willReturn(1);

        $sizeProductValue->getOption()->willReturn('XL');
        $colorProductValue->getOption()->willReturn('Red');

        $redTShirtProduct2->getGroups()->willReturn([$clothesVariantGroup]);
        $redTShirtProduct2->getValue('size')->willReturn($sizeProductValue);
        $redTShirtProduct2->getValue('color')->willReturn($colorProductValue);
        $redTShirtProduct2->getId()->willReturn(2);

        $criteria =
            [
                [
                    'attribute' => $sizeAttribute,
                    'option' => 'XL'
                ],
                [
                    'attribute' => $colorAttribute,
                    'option' => 'Red'
                ]
            ];

        $productRepository->findAllForVariantGroup($tShirtVariantGroup, $criteria)->willReturn([]);
        $productRepository->findAllForVariantGroup($clothesVariantGroup, $criteria)->willReturn([$redTShirtProduct2]);

        $context->addViolation(
            'Group "%variant group%" already contains another product with values "%values%"',
            [
                '%variant group%' => 'Clothes',
                '%values%' => 'size: XL, color: Red',
            ]
        )->shouldBeCalled();

        $this->validate($redTShirtProduct, $uniqueVariantAxisConstraint);
    }
}
