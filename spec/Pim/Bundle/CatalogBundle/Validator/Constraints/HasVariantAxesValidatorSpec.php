<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\HasVariantAxes;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class HasVariantAxesValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\HasVariantAxesValidator');
    }

    function it_does_not_add_violation_product_without_variant_group(
        $context,
        ProductInterface $product,
        Constraint $constraint
    ) {
        $product->getVariantGroup()->willReturn(null);
        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($product, $constraint);
    }

    function it_validates_product_has_all_the_axes_of_its_variant_group(
        $context,
        ProductInterface $product,
        GroupInterface $tShirtVariantGroup,
        AttributeInterface $sizeAttribute,
        AttributeInterface $colorAttribute,
        ProductValueInterface $sizeValue,
        ProductValueInterface $colorValue,
        Constraint $constraint
    ) {
        $tShirtVariantGroup->getAxisAttributes()->willReturn([$sizeAttribute, $colorAttribute]);
        $sizeAttribute->getCode()->willReturn('size');
        $colorAttribute->getCode()->willReturn('color');

        $product->getVariantGroup()->willReturn($tShirtVariantGroup);
        $product->getValue('size')->willReturn($sizeValue);
        $product->getValue('color')->willReturn($colorValue);

        $sizeValue->getData()->willReturn('XL');
        $colorValue->getData()->willReturn('Red');

        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($product, $constraint);
    }

    function it_adds_a_violation_when_validates_a_product_with_missing_value_for_an_axe_of_its_variant_group(
        $context,
        ProductInterface $product,
        GroupInterface $tShirtVariantGroup,
        AttributeInterface $sizeAttribute,
        AttributeInterface $colorAttribute,
        ProductValueInterface $sizeValue,
        ProductValueInterface $identifierValue,
        HasVariantAxes $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $tShirtVariantGroup->getCode()->willReturn('tshirt');
        $tShirtVariantGroup->getAxisAttributes()->willReturn([$sizeAttribute, $colorAttribute]);
        $sizeAttribute->getCode()->willReturn('size');
        $colorAttribute->getCode()->willReturn('color');

        $product->getIdentifier()->willReturn($identifierValue);
        $product->getVariantGroup()->willReturn($tShirtVariantGroup);

        $product->getValue('size')->willReturn($sizeValue);
        $product->getValue('color')->willReturn(null);

        $sizeValue->getData()->willReturn('XL');

        $context->buildViolation(
            'The product "%product%" is in the variant group "%variant%" but it misses the following axes: %axes%.',
            [
                '%product%' => $identifierValue,
                '%variant%' => 'tshirt',
                '%axes%'    => 'color'
            ]
        )
        ->shouldBeCalled()
        ->willReturn($violation);

        $this->validate($product, $constraint);
    }
}
