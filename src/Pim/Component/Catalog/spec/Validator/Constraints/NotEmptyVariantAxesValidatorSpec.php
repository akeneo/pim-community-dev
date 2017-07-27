<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\FamilyVariant\CanHaveFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CanHaveFamilyVariantInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Validator\Constraints\NotEmptyVariantAxes;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotEmptyVariantAxesValidatorSpec extends ObjectBehavior
{
    function let(
        CanHaveFamilyVariantAttributesProvider $axesProvider,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($axesProvider);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Validator\Constraints\NotEmptyVariantAxesValidator');
    }

    function it_throws_an_exception_if_the_entity_is_not_supported(
        \DateTime $entity,
        NotEmptyVariantAxes $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$entity, $constraint]);
    }

    function it_throws_an_exception_if_the_constraint_is_not_supported(
        CanHaveFamilyVariantInterface $entity,
        Constraint $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$entity, $constraint]);
    }

    function it_raises_no_violation_if_the_entity_has_no_family_variant(
        $context,
        CanHaveFamilyVariantInterface $entity,
        NotEmptyVariantAxes $constraint
    ) {
        $entity->getFamilyVariant()->willReturn(null);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_the_entity_has_no_axis(
        $axesProvider,
        $context,
        CanHaveFamilyVariantInterface $entity,
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
        CanHaveFamilyVariantInterface $entity,
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

    function it_raises_a_violation_if_the_entity_has_no_value_for_an_axis(
        $axesProvider,
        $context,
        CanHaveFamilyVariantInterface $entity,
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
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }
}
