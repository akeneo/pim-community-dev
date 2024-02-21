<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotEmptyVariantAxesValidator;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotEmptyVariantAxes;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotEmptyVariantAxesValidatorSpec extends ObjectBehavior
{
    function let(
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($axesProvider);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotEmptyVariantAxesValidator::class);
    }

    function it_throws_an_exception_if_the_entity_is_not_supported(
        \DateTime $entity,
        NotEmptyVariantAxes $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$entity, $constraint]);
    }

    function it_throws_an_exception_if_the_constraint_is_not_supported(
        EntityWithFamilyVariantInterface $entity,
        Constraint $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$entity, $constraint]);
    }

    function it_raises_no_violation_if_the_entity_has_no_family_variant(
        $context,
        EntityWithFamilyVariantInterface $entity,
        NotEmptyVariantAxes $constraint
    ) {
        $entity->getFamilyVariant()->willReturn(null);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_the_entity_has_no_axis(
        $axesProvider,
        $context,
        EntityWithFamilyVariantInterface $entity,
        FamilyVariantInterface $familyVariant,
        NotEmptyVariantAxes $constraint
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $axesProvider->getAxes($entity)->willReturn([]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_the_entity_has_a_value_for_all_its_axes(
        $axesProvider,
        $context,
        EntityWithFamilyVariantInterface $entity,
        FamilyVariantInterface $familyVariant,
        NotEmptyVariantAxes $constraint,
        AttributeInterface $color,
        ValueInterface $red
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $axesProvider->getAxes($entity)->willReturn([$color]);
        $color->getCode()->willReturn('color');
        $entity->getValue('color')->willReturn($red);
        $red->getData()->willReturn('red');

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_the_entity_has_zero_as_value_for_its_axis(
        $axesProvider,
        $context,
        EntityWithFamilyVariantInterface $entity,
        FamilyVariantInterface $familyVariant,
        NotEmptyVariantAxes $constraint,
        AttributeInterface $size,
        ValueInterface $zero
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $axesProvider->getAxes($entity)->willReturn([$size]);
        $size->getCode()->willReturn('size');
        $entity->getValue('size')->willReturn($zero);
        $zero->getData()->willReturn('0');

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_the_entity_has_no_value_for_an_axis(
        $axesProvider,
        $context,
        EntityWithFamilyVariantInterface $entity,
        FamilyVariantInterface $familyVariant,
        NotEmptyVariantAxes $constraint,
        AttributeInterface $color,
        ConstraintViolationBuilderInterface $violation
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $axesProvider->getAxes($entity)->willReturn([$color]);
        $color->getCode()->willReturn('color');
        $entity->getValue('color')->willReturn(null);

        $context
            ->buildViolation(
                NotEmptyVariantAxes::EMPTY_AXIS_VALUE, [
                    '%attribute%' => 'color'
                ]
            )
            ->willReturn($violation);
        $violation->atPath('attribute')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_the_entity_has_no_value_for_an_metric_axis(
        $axesProvider,
        $context,
        EntityWithFamilyVariantInterface $entity,
        FamilyVariantInterface $familyVariant,
        NotEmptyVariantAxes $constraint,
        AttributeInterface $attribute,
        MetricInterface $metric,
        ValueInterface $value,
        ConstraintViolationBuilderInterface $violation
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $axesProvider->getAxes($entity)->willReturn([$attribute]);

        $value->getData()->willReturn($metric);
        $metric->getData()->willReturn(null);
        $attribute->getCode()->willReturn('display_diagonal');
        $entity->getValue('display_diagonal')->willReturn(null);

        $context
            ->buildViolation(
                NotEmptyVariantAxes::EMPTY_AXIS_VALUE, [
                    '%attribute%' => 'display_diagonal'
                ]
            )
            ->willReturn($violation);
        $violation->atPath('attribute')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }
}
