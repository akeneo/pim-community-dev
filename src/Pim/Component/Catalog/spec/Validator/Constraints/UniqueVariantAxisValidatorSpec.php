<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Validator\Constraints\UniqueVariantAxis;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueVariantAxisValidatorSpec extends ObjectBehavior
{
    function let(ProductRepositoryInterface $productRepository, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($productRepository);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Validator\Constraints\UniqueVariantAxisValidator');
    }

    function it_validates_product_with_no_groups(
        $context,
        ProductInterface $product,
        UniqueVariantAxis $uniqueVariantAxisConstraint
    ) {
        $product->getVariantGroup()->willReturn(null);
        $context
            ->buildViolation(Argument::cetera())
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
        $context->buildViolation(Argument::cetera())
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
            ->buildViolation(Argument::cetera())
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
        UniqueVariantAxis $uniqueVariantAxisConstraint,
        ConstraintViolationBuilderInterface $violation
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

        $context->buildViolation(
            'Group "%variant group%" already contains another product with values "%values%"',
            [
                '%variant group%' => 'Groupe TShirt',
                '%values%' => 'size: XL, color: Red',
            ]
        )->shouldBeCalled()
        ->willReturn($violation);

        $this->validate($tShirtVariantGroup, $uniqueVariantAxisConstraint);
    }

    function it_does_not_add_violation_when_product_has_no_groups(
        $context,
        ProductInterface $product,
        UniqueVariantAxis $uniqueVariantAxisConstraint
    ) {
        $product->getVariantGroup()->willReturn(null);

        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($product, $uniqueVariantAxisConstraint);
    }

    function it_does_not_add_violation_when_validating_product_in_groups_with_unique_combination_of_axis_attributes(
        $context,
        $productRepository,
        GroupInterface $tShirtVariantGroup,
        GroupTypeInterface $tShirtGroupType,
        ProductInterface $redTShirtProduct,
        AttributeInterface $sizeAttribute,
        AttributeInterface $colorAttribute,
        ProductValueInterface $sizeProductValue,
        ProductValueInterface $redColorProductValue,
        UniqueVariantAxis $uniqueVariantAxisConstraint
    ) {
        $tShirtVariantGroup->getId()->willReturn(1);
        $tShirtVariantGroup->getLabel()->willReturn('TShirts');
        $tShirtVariantGroup->getType()->willReturn($tShirtGroupType);
        $tShirtGroupType->isVariant()->willReturn(true);
        $tShirtVariantGroup->getAxisAttributes()->willReturn([$sizeAttribute, $colorAttribute]);

        $sizeAttribute->getCode()->willReturn('size');
        $sizeAttribute->isBackendTypeReferenceData()->willReturn(false);
        $colorAttribute->getCode()->willReturn('color');
        $colorAttribute->isBackendTypeReferenceData()->willReturn(false);

        $redTShirtProduct->getVariantGroup()->willReturn($tShirtVariantGroup);
        $redTShirtProduct->getId()->willReturn(1);
        $redTShirtProduct->getValue('size')->willReturn($sizeProductValue);
        $redTShirtProduct->getValue('color')->willReturn($redColorProductValue);
        $sizeProductValue->getOption()->willReturn('XL');
        $redColorProductValue->getOption()->willReturn('Red');

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

        $productRepository->findProductIdsForVariantGroup($tShirtVariantGroup, $criteria)->willReturn([]);

        $context->buildViolation()->shouldNotBeCalled(Argument::cetera());

        $this->validate($redTShirtProduct, $uniqueVariantAxisConstraint);
    }

    function it_adds_a_violation_when_validating_product_in_groups_with_non_unique_combination_of_axis_attributes(
        $context,
        $productRepository,
        GroupInterface $tShirtVariantGroup,
        ProductInterface $redTShirtProduct,
        AttributeInterface $sizeAttribute,
        AttributeInterface $colorAttribute,
        ProductValueInterface $sizeProductValue,
        ProductValueInterface $colorProductValue,
        UniqueVariantAxis $uniqueVariantAxisConstraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $redTShirtProduct->getVariantGroup()->willReturn($tShirtVariantGroup);

        $tShirtVariantGroup->getId()->willReturn(1);
        $tShirtVariantGroup->getLabel()->willReturn('TShirts');
        $tShirtVariantGroup->getAxisAttributes()->willReturn([$sizeAttribute, $colorAttribute]);

        $sizeAttribute->getCode()->willReturn('size');
        $sizeAttribute->isBackendTypeReferenceData()->willReturn(true);
        $sizeAttribute->getReferenceDataName()->willReturn('ref_size');
        $colorAttribute->getCode()->willReturn('color');
        $colorAttribute->isBackendTypeReferenceData()->willReturn(true);
        $colorAttribute->getReferenceDataName()->willReturn('ref_color');

        $redTShirtProduct->getValue('size')->willReturn($sizeProductValue);
        $redTShirtProduct->getValue('color')->willReturn($colorProductValue);
        $redTShirtProduct->getId()->willReturn(1);

        $sizeProductValue->getData()->willReturn('XL');
        $sizeProductValue->getOption()->willReturn(null);
        $colorProductValue->getData()->willReturn('Red');
        $colorProductValue->getOption()->willReturn(null);

        $criteria =
            [
                [
                    'attribute' => $sizeAttribute,
                    'referenceData' => [
                        'name' => 'ref_size',
                        'data' => 'XL',
                    ]
                ],
                [
                    'attribute' => $colorAttribute,
                    'referenceData' => [
                        'name' => 'ref_color',
                        'data' => 'Red',
                    ]
                ]
            ];

        $productRepository->findProductIdsForVariantGroup($tShirtVariantGroup, $criteria)->shouldBeCalled()->willReturn(['id' => 1]);

        $context->buildViolation(
            'Group "%variant group%" already contains another product with values "%values%"',
            [
                '%variant group%' => 'TShirts',
                '%values%' => 'size: XL, color: Red',
            ]
        )->shouldBeCalled()
        ->willReturn($violation);

        $this->validate($redTShirtProduct, $uniqueVariantAxisConstraint);
    }
}
