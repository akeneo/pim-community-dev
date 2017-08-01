<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Validator\Constraints\OnlyExpectedAttributes;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class OnlyExpectedAttributesValidatorSpec extends ObjectBehavior
{
    function let(
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($attributesProvider);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Validator\Constraints\OnlyExpectedAttributesValidator');
    }

    function it_throws_an_exception_if_the_entity_is_not_supported(
        \DateTime $entity,
        OnlyExpectedAttributes $constraint
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
        OnlyExpectedAttributes $constraint
    ) {
        $entity->getFamilyVariant()->willReturn(null);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_it_has_no_attribute_on_its_level(
        $context,
        $attributesProvider,
        FamilyVariantInterface $familyVariant,
        EntityWithFamilyVariantInterface $entity,
        OnlyExpectedAttributes $constraint,
        AttributeInterface $color
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $entity->getAttributes()->willReturn([]);
        $attributesProvider->getAttributes($entity)->willReturn([$color]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_value_in_unexpected_attribute(
        $context,
        $attributesProvider,
        FamilyVariantInterface $familyVariant,
        EntityWithFamilyVariantInterface $entity,
        OnlyExpectedAttributes $constraint,
        AttributeInterface $color,
        AttributeInterface $sku,
        ConstraintViolationBuilderInterface $violation
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $entity->getAttributes()->willReturn([$color, $sku]);
        $attributesProvider->getAttributes($entity)->willReturn([$color]);
        $sku->getCode()->willReturn('sku');

        $context
            ->buildViolation(
                OnlyExpectedAttributes::ATTRIBUTE_UNEXPECTED, [
                    '%attribute%' => 'sku'
                ]
            )
            ->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }
}
