<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ValueCollection;
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
        AttributeInterface $color,
        FamilyInterface $family,
        Collection $attributes,
        ValueCollection $valuesForVariation
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $entity->getValuesForVariation()->willReturn($valuesForVariation);
        $valuesForVariation->getAttributes()->willReturn([]);
        $attributesProvider->getAttributes($entity)->willReturn([$color]);

        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributes()->willReturn($attributes);
        $attributes->contains($color)->willReturn(true);

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
        AttributeInterface $price,
        ConstraintViolationBuilderInterface $violation,
        FamilyInterface $family,
        Collection $attributes,
        ValueCollection $valuesForVariation
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $entity->getValuesForVariation()->willReturn($valuesForVariation);
        $valuesForVariation->getAttributes()->willReturn([$color, $sku, $price]);

        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributes()->willReturn($attributes);
        $family->getCode()->willReturn('family');

        $attributes->contains($color)->willReturn(true);
        $attributes->contains($sku)->willReturn(true);
        $attributes->contains($price)->willReturn(false);

        $attributesProvider->getAttributes($entity)->willReturn([$color]);
        $sku->getCode()->willReturn('sku');
        $price->getCode()->willReturn('price');

        $context
            ->buildViolation(
                OnlyExpectedAttributes::ATTRIBUTE_DOES_NOT_BELONG_TO_FAMILY, [
                    '%attribute%' => 'price',
                    '%family%' => 'family'
                ]
            )
            ->willReturn($violation);

        $context
            ->buildViolation(
                OnlyExpectedAttributes::ATTRIBUTE_UNEXPECTED, [
                    '%attribute%' => 'sku'
                ]
            )
            ->willReturn($violation);

        $violation->atPath('attribute')->willReturn($violation)->shouldBeCalledTimes(2);
        $violation->addViolation()->shouldBeCalledTimes(2);

        $this->validate($entity, $constraint);
    }
}
