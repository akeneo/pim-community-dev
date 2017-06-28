<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Validator\Constraints\FamilyVariant;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Validator\Constraints\FamilyVariantValidator;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class FamilyVariantValidatorSpec extends ObjectBehavior
{
    function let(TranslatorInterface $translator)
    {
        $this->beConstructedWith($translator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantValidator::class);
    }

    function it_is_a_validator()
    {
        $this->shouldHaveType(ConstraintValidator::class);
    }

    function it_validates_family_variant_axes(
        FamilyVariantInterface $familyVariant,
        FamilyVariant $constraint,
        ArrayCollection $axes,
        ArrayCollection $attributes,
        AttributeInterface $color,
        AttributeInterface $size,
        \Iterator $iterator
    ) {
        $color->getCode()->willReturn('color');
        $color->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $size->getCode()->willReturn('size');
        $size->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);

        $axes->getIterator()->willReturn($iterator);
        $iterator->valid()->willReturn(true, true, false);
        $iterator->current()->willReturn($color, $size);
        $iterator->rewind()->shouldBeCalled();
        $iterator->next()->shouldBeCalled();

        $familyVariant->getAxes()->willReturn($axes);

        $familyVariant->getAttributes()->willReturn($attributes);
        $attributes->map(Argument::any())->willReturn($attributes);
        $attributes->toArray()->willreturn(['color', 'size']);

        $this->validate($familyVariant, $constraint);
    }

    function it_add_violations_when_axes_are_invalid(
        $translator,
        FamilyVariantInterface $familyVariant,
        FamilyVariant $constraint,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        ArrayCollection $axes,
        AttributeInterface $color,
        AttributeInterface $size,
        AttributeInterface $weatherCondition,
        \Iterator $iterator,
        ArrayCollection $attributes
    ) {
        $this->initialize($context);

        $color->getCode()->willReturn('color');
        $color->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $size->getCode()->willReturn('size');
        $size->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $weatherCondition->getCode()->willReturn('weather_conditions');
        $weatherCondition->getType()->willReturn(AttributeTypes::BACKEND_TYPE_DATE);

        $axes->getIterator()->willReturn($iterator);
        $iterator->valid()->willReturn(true, true, true, false);
        $iterator->current()->willReturn($color, $size, $weatherCondition);
        $iterator->rewind()->shouldBeCalled();
        $iterator->next()->shouldBeCalled();

        $familyVariant->getAxes()->willReturn($axes);

        $familyVariant->getAttributes()->willReturn($attributes);
        $attributes->map(Argument::any())->willReturn($attributes);
        $attributes->toArray()->willreturn(['color', 'size', 'weather_conditions']);

        $translator->trans('pim_catalog.constraint.family_variant_axes_unique')
            ->willReturn('family_variant_axes_unique');
        $translator->trans('pim_catalog.constraint.family_variant_axes_type')
            ->willReturn('family_variant_axes_type');

        $context->buildViolation('family_variant_axes_unique')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $context->buildViolation('family_variant_axes_type')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($familyVariant, $constraint);
    }

    function it_add_violations_when_attributes_are_invalid(
        $translator,
        FamilyVariantInterface $familyVariant,
        FamilyVariant $constraint,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
        ArrayCollection $axes,
        AttributeInterface $color,
        AttributeInterface $size,
        \Iterator $iterator,
        ArrayCollection $attributes
    ) {
        $this->initialize($context);

        $color->getCode()->willReturn('color');
        $color->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $size->getCode()->willReturn('size');
        $size->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);

        $axes->getIterator()->willReturn($iterator);
        $iterator->valid()->willReturn(true, true, false);
        $iterator->current()->willReturn($color, $size);
        $iterator->rewind()->shouldBeCalled();
        $iterator->next()->shouldBeCalled();

        $familyVariant->getAxes()->willReturn($axes);

        $familyVariant->getAttributes()->willReturn($attributes);
        $attributes->map(Argument::any())->willReturn($attributes);
        $attributes->toArray()->willreturn(['color', 'size', 'size']);

        $translator->trans('pim_catalog.constraint.family_variant_attributes_unique')
            ->willReturn('family_variant_attributes_unique');

        $context->buildViolation('family_variant_attributes_unique')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($familyVariant, $constraint);
    }

    function it_only_works_with_family_variant_object(FamilyVariant $constraint, ProductInterface $product)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$product, $constraint]);
    }

    function it_only_works_with_family_variant_axes_constraint(NotBlank $constraint, FamilyVariantInterface $product)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$product, $constraint]);
    }
}
