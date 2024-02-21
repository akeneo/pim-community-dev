<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyAttributeUsedAsAxis;
use Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyAttributeUsedAsAxisValidator;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class FamilyAttributeUsedAsAxisValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyAttributeUsedAsAxisValidator::class);
    }

    function it_only_supports_constraint_family_attributes_used_as_axis(
        $context,
        FamilyAttributeUsedAsAxis $familyAttributesUsedAsAxisConstraint,
        FamilyInterface $family,
        Collection $familyVariants,
        \ArrayIterator $familyVariantIterator
    ) {
        $family->getFamilyVariants()->willReturn($familyVariants);
        $familyVariants->getIterator()->willReturn($familyVariantIterator);
        $familyVariantIterator->rewind()->shouldBeCalled();
        $familyVariantIterator->next()->shouldNotBeCalled();
        $familyVariantIterator->valid()->willReturn(false);

        $this->validate($family, $familyAttributesUsedAsAxisConstraint);
    }

    function it_does_not_support_other_constraint_family_attributes_used_as_axis_constraint(
        Constraint $otherConstraint,
        FamilyInterface $family
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$family, $otherConstraint]);
    }

    function it_builds_violation_for_an_attribute_used_as_axis_in_one_family_variant(
        $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        FamilyAttributeUsedAsAxis $familyAttributesUsedAsAxisConstraint,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        Collection $familyVariants,
        \ArrayIterator $familyVariantIterator,
        Collection $axisAttributes
    ) {
        $family->getAttributeCodes()->willReturn(['common_attribute_1', 'common_attribute_2']);

        $family->getFamilyVariants()->willReturn($familyVariants);
        $familyVariants->getIterator()->willReturn($familyVariantIterator);
        $familyVariantIterator->current()->willReturn($familyVariant);
        $familyVariantIterator->valid()->willReturn(true, false);
        $familyVariantIterator->rewind()->shouldBeCalled();
        $familyVariantIterator->next()->shouldBeCalled();

        $familyVariant->getCode()->willReturn('my_family_variant');
        $familyVariant->getAxes()->willReturn($axisAttributes);
        $axisAttributes->map(Argument::cetera())->willReturn($axisAttributes);
        $axisAttributes->toArray()->willReturn(['attribute_used_as_axis_code']);

        $context->buildViolation(
            $familyAttributesUsedAsAxisConstraint->messageAttribute,
            [
                '%attribute%'      => 'attribute_used_as_axis_code',
                '%family_variant%' => 'my_family_variant',
            ]
        )->willReturn($violationBuilder);
        $violationBuilder->atPath('attributes')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($family, $familyAttributesUsedAsAxisConstraint);
    }

    function it_builds_violations_for_an_attribute_used_as_axis_in_multiple_family_variants(
        $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        FamilyAttributeUsedAsAxis $familyAttributesUsedAsAxisConstraint,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant1,
        FamilyVariantInterface $familyVariant2,
        Collection $familyVariants,
        \ArrayIterator $familyVariantIterator,
        Collection $axisAttributes1,
        Collection $axisAttributes2
    ) {
        $family->getAttributeCodes()->willReturn(['common_attribute_1', 'common_attribute_2', 'axis_attribute']);

        $family->getFamilyVariants()->willReturn($familyVariants);
        $familyVariants->getIterator()->willReturn($familyVariantIterator);
        $familyVariantIterator->current()->willReturn($familyVariant1, $familyVariant2);
        $familyVariantIterator->valid()->willReturn(true, false);
        $familyVariantIterator->rewind()->shouldBeCalled();
        $familyVariantIterator->next()->shouldBeCalled();

        $familyVariant1->getCode()->willReturn('my_family_variant_1');
        $familyVariant1->getAxes()->willReturn($axisAttributes1);
        $axisAttributes1->map(Argument::cetera())->willReturn($axisAttributes1);
        $axisAttributes1->toArray()->willReturn(['attribute_used_as_axis_code_1', 'axis_attribute']);

        $familyVariant2->getCode()->willReturn('my_family_variant_2');
        $familyVariant2->getAxes()->willReturn($axisAttributes2);
        $axisAttributes2->map(Argument::cetera())->willReturn($axisAttributes2);
        $axisAttributes2->toArray()->willReturn(['axis_attribute', 'attribute_used_as_axis_code_2']);

        $context->buildViolation(
            $familyAttributesUsedAsAxisConstraint->messageAttribute,
            [
                '%attribute%'      => 'attribute_used_as_axis_code_1',
                '%family_variant%' => 'my_family_variant_1',
            ]
        )->willReturn($violationBuilder);
        $context->buildViolation(
            $familyAttributesUsedAsAxisConstraint->messageAttribute,
            [
                '%attribute%'      => 'attribute_used_as_axis_code_2',
                '%family_variant%' => 'my_family_variant_2',
            ]
        )->willReturn($violationBuilder);
        $violationBuilder->atPath('attributes')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($family, $familyAttributesUsedAsAxisConstraint);
    }

    function it_builds_violations_for_multiple_attributes_used_as_axis_one_family_variant(
        $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        FamilyAttributeUsedAsAxis $familyAttributesUsedAsAxisConstraint,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        Collection $familyVariants,
        \ArrayIterator $familyVariantIterator,
        Collection $axisAttributes
    ) {
        $family->getAttributeCodes()->willReturn(['common_attribute_1', 'common_attribute_2']);

        $family->getFamilyVariants()->willReturn($familyVariants);
        $familyVariants->getIterator()->willReturn($familyVariantIterator);
        $familyVariantIterator->current()->willReturn($familyVariant);
        $familyVariantIterator->valid()->willReturn(true, false);
        $familyVariantIterator->rewind()->shouldBeCalled();
        $familyVariantIterator->next()->shouldBeCalled();

        $familyVariant->getCode()->willReturn('my_family_variant');
        $familyVariant->getAxes()->willReturn($axisAttributes);
        $axisAttributes->map(Argument::cetera())->willReturn($axisAttributes);
        $axisAttributes->toArray()->willReturn(['attribute_used_as_axis_code_1', 'attribute_used_as_axis_code_2']);

        $context->buildViolation(
            $familyAttributesUsedAsAxisConstraint->messageAttribute,
            [
                '%attribute%'      => 'attribute_used_as_axis_code_1',
                '%family_variant%' => 'my_family_variant',
            ]
        )->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $context->buildViolation(
            $familyAttributesUsedAsAxisConstraint->messageAttribute,
            [
                '%attribute%'      => 'attribute_used_as_axis_code_2',
                '%family_variant%' => 'my_family_variant',
            ]
        )->willReturn($violationBuilder);
        $violationBuilder->atPath('attributes')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($family, $familyAttributesUsedAsAxisConstraint);
    }

    function it_builds_violations_for_multiple_attributes_used_as_axis_in_multiple_family_variants(
        $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        FamilyAttributeUsedAsAxis $familyAttributesUsedAsAxisConstraint,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant1,
        FamilyVariantInterface $familyVariant2,
        Collection $familyVariants,
        \ArrayIterator $familyVariantIterator,
        Collection $axisAttributes1,
        Collection $axisAttributes2
    ) {
        $family->getAttributeCodes()->willReturn(['common_attribute_1', 'common_attribute_2']);

        $family->getFamilyVariants()->willReturn($familyVariants);
        $familyVariants->getIterator()->willReturn($familyVariantIterator);
        $familyVariantIterator->current()->willReturn($familyVariant1, $familyVariant2);
        $familyVariantIterator->valid()->willReturn(true, false);
        $familyVariantIterator->rewind()->shouldBeCalled();
        $familyVariantIterator->next()->shouldBeCalled();

        $familyVariant1->getCode()->willReturn('my_family_variant_1');
        $familyVariant1->getAxes()->willReturn($axisAttributes1);
        $axisAttributes1->map(Argument::cetera())->willReturn($axisAttributes1);
        $axisAttributes1->toArray()->willReturn(['attribute_used_as_axis_code_1', 'attribute_used_as_axis_code_2']);

        $familyVariant2->getCode()->willReturn('my_family_variant_2');
        $familyVariant2->getAxes()->willReturn($axisAttributes2);
        $axisAttributes2->map(Argument::cetera())->willReturn($axisAttributes2);
        $axisAttributes2->toArray()->willReturn(['attribute_used_as_axis_code_1', 'attribute_used_as_axis_code_2']);

        $context->buildViolation(
            $familyAttributesUsedAsAxisConstraint->messageAttribute,
            [
                '%attribute%'      => 'attribute_used_as_axis_code_1',
                '%family_variant%' => 'my_family_variant_1',
            ]
        )->willReturn($violationBuilder);
        $context->buildViolation(
            $familyAttributesUsedAsAxisConstraint->messageAttribute,
            [
                '%attribute%'      => 'attribute_used_as_axis_code_2',
                '%family_variant%' => 'my_family_variant_1',
            ]
        )->willReturn($violationBuilder);
        $context->buildViolation(
            $familyAttributesUsedAsAxisConstraint->messageAttribute,
            [
                '%attribute%'      => 'attribute_used_as_axis_code_1',
                '%family_variant%' => 'my_family_variant_2',
            ]
        )->willReturn($violationBuilder);
        $context->buildViolation(
            $familyAttributesUsedAsAxisConstraint->messageAttribute,
            [
                '%attribute%'      => 'attribute_used_as_axis_code_2',
                '%family_variant%' => 'my_family_variant_2',
            ]
        )->willReturn($violationBuilder);
        $violationBuilder->atPath('attributes')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($family, $familyAttributesUsedAsAxisConstraint);
    }

    function it_does_not_build_any_violation(
        $context,
        FamilyAttributeUsedAsAxis $familyAttributesUsedAsAxisConstraint,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant1,
        FamilyVariantInterface $familyVariant2,
        Collection $familyVariants,
        \ArrayIterator $familyVariantIterator,
        Collection $axisAttributes1,
        Collection $axisAttributes2
    ) {
        $family->getAttributeCodes()->willReturn([
            'common_attribute_1',
            'common_attribute_2',
            'attribute_used_as_axis_code_1',
            'attribute_used_as_axis_code_2',
            'common_attributes_3',
            'common_attributes_4',
        ]);

        $family->getFamilyVariants()->willReturn($familyVariants);
        $familyVariants->getIterator()->willReturn($familyVariantIterator);
        $familyVariantIterator->current()->willReturn($familyVariant1, $familyVariant2);
        $familyVariantIterator->valid()->willReturn(true, false);
        $familyVariantIterator->rewind()->shouldBeCalled();
        $familyVariantIterator->next()->shouldBeCalled();

        $familyVariant1->getCode()->willReturn('my_family_variant_1');
        $familyVariant1->getAxes()->willReturn($axisAttributes1);
        $axisAttributes1->map(Argument::cetera())->willReturn($axisAttributes1);
        $axisAttributes1->toArray()->willReturn(['attribute_used_as_axis_code_1', 'attribute_used_as_axis_code_2']);

        $familyVariant2->getCode()->willReturn('my_family_variant_2');
        $familyVariant2->getAxes()->willReturn($axisAttributes2);
        $axisAttributes2->map(Argument::cetera())->willReturn($axisAttributes2);
        $axisAttributes2->toArray()->willReturn(['attribute_used_as_axis_code_1', 'attribute_used_as_axis_code_2']);

        $context->buildViolation()->shouldNotBeCalled();

        $this->validate($family, $familyAttributesUsedAsAxisConstraint);
    }
}
