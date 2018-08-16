<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueVariantAxisValidator;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\EntityWithFamilyVariantRepository;
use Akeneo\Pim\Enrichment\Component\Product\Exception\AlreadyExistingAxisValueCombinationException;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueVariantAxis;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueAxesCombinationSet;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueVariantAxisValidatorSpec extends ObjectBehavior
{
    function let(
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        EntityWithFamilyVariantRepository $repository,
        UniqueAxesCombinationSet $uniqueAxesCombinationSet,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($axesProvider, $repository, $uniqueAxesCombinationSet);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueVariantAxisValidator::class);
    }

    function it_throws_an_exception_if_the_entity_is_not_supported(
        \DateTime $entity,
        UniqueVariantAxis $constraint
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
        UniqueVariantAxis $constraint
    ) {
        $entity->getFamilyVariant()->willReturn(null);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_the_entity_has_no_parent(
        $context,
        FamilyVariantInterface $familyVariant,
        EntityWithFamilyVariantInterface $entity,
        UniqueVariantAxis $constraint
    ) {
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $entity->getParent()->willReturn(null);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_the_entity_has_no_sibling(
        $context,
        $repository,
        $axesProvider,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        EntityWithFamilyVariantInterface $entity,
        UniqueVariantAxis $constraint
    ) {
        $entity->getParent()->willReturn($parent);
        $axesProvider->getAxes($entity)->willReturn([]);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $repository->findSiblings($entity)->willReturn([]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_the_entity_has_no_axis(
        $context,
        $repository,
        $axesProvider,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        EntityWithFamilyVariantInterface $entity,
        EntityWithFamilyVariantInterface $sibling,
        UniqueVariantAxis $constraint
    ) {
        $entity->getParent()->willReturn($parent);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $axesProvider->getAxes($entity)->willReturn([]);
        $repository->findSiblings($entity)->willReturn([$sibling]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_axes_combination_is_empty(
        $context,
        $repository,
        $axesProvider,
        $uniqueAxesCombinationSet,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        ProductModelInterface $entity,
        ProductModelInterface $sibling1,
        ProductModelInterface $sibling2,
        AttributeInterface $color,
        ValueInterface $emptyValue,
        UniqueVariantAxis $constraint
    ) {
        $entity->getParent()->willReturn($parent);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $axesProvider->getAxes($entity)->willReturn([$color]);
        $color->getCode()->willReturn('color');
        $repository->findSiblings($entity)->willReturn([$sibling1, $sibling2]);

        $entity->getValue('color')->willReturn($emptyValue);
        $emptyValue->__toString()->willReturn('');
        $uniqueAxesCombinationSet->addCombination(Argument::any())->shouldNotBeCalled();

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_there_is_no_duplicate_in_any_sibling_product_model_from_database(
        $context,
        $repository,
        $axesProvider,
        $uniqueAxesCombinationSet,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        ProductModelInterface $entity,
        ProductModelInterface $sibling1,
        ProductModelInterface $sibling2,
        AttributeInterface $color,
        ValueInterface $blue,
        ValueInterface $red,
        ValueInterface $yellow,
        UniqueVariantAxis $constraint
    ) {
        $entity->getParent()->willReturn($parent);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $axesProvider->getAxes($entity)->willReturn([$color]);
        $color->getCode()->willReturn('color');

        $repository->findSiblings($entity)->willReturn([$sibling1, $sibling2]);
        $sibling1->getCode()->willReturn('sibling1');
        $sibling2->getCode()->willReturn('sibling2');

        $entity->getValue('color')->willReturn($blue);
        $sibling1->getValue('color')->willReturn($red);
        $sibling2->getValue('color')->willReturn($yellow);

        $blue->__toString()->willReturn('[blue]');
        $red->__toString()->willReturn('[red]');
        $yellow->__toString()->willReturn('[yellow]');

        $uniqueAxesCombinationSet->addCombination($entity, '[blue]')->shouldBeCalled();

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_there_is_no_duplicate_in_any_sibling_variant_product_from_database(
        $context,
        $repository,
        $axesProvider,
        $uniqueAxesCombinationSet,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        ProductInterface $entity,
        ProductInterface $sibling1,
        ProductInterface $sibling2,
        AttributeInterface $color,
        ValueInterface $blue,
        ValueInterface $red,
        ValueInterface $yellow,
        UniqueVariantAxis $constraint
    ) {
        $entity->getParent()->willReturn($parent);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $axesProvider->getAxes($entity)->willReturn([$color]);
        $color->getCode()->willReturn('color');

        $repository->findSiblings($entity)->willReturn([$sibling1, $sibling2]);
        $sibling1->getIdentifier()->willReturn('sibling1');
        $sibling2->getIdentifier()->willReturn('sibling2');

        $entity->getValue('color')->willReturn($blue);
        $sibling1->getValue('color')->willReturn($red);
        $sibling2->getValue('color')->willReturn($yellow);

        $blue->__toString()->willReturn('[blue]');
        $red->__toString()->willReturn('[red]');
        $yellow->__toString()->willReturn('[yellow]');

        $uniqueAxesCombinationSet->addCombination($entity, '[blue]')->shouldBeCalled();

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicate_value_in_any_sibling_product_model(
        $context,
        $repository,
        $axesProvider,
        $uniqueAxesCombinationSet,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        ProductModelInterface $entity,
        ProductModelInterface $sibling1,
        ProductModelInterface $sibling2,
        AttributeInterface $color,
        ValueInterface $blue,
        ValueInterface $yellow,
        UniqueVariantAxis $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $entity->getCode()->willReturn('entity_code');
        $entity->getParent()->willReturn($parent);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $axesProvider->getAxes($entity)->willReturn([$color]);
        $color->getCode()->willReturn('color');

        $repository->findSiblings($entity)->willReturn([$sibling1, $sibling2]);
        $sibling1->getCode()->willReturn('sibling1');
        $sibling2->getCode()->willReturn('sibling2');

        $entity->getValue('color')->willReturn($blue);
        $sibling1->getValue('color')->willReturn($blue);
        $sibling2->getValue('color')->willReturn($yellow);

        $blue->__toString()->willReturn('[blue]');
        $yellow->__toString()->willReturn('[yellow]');

        $uniqueAxesCombinationSet->addCombination($entity, '[blue]')->shouldBeCalled();

        $context
            ->buildViolation(
                UniqueVariantAxis::DUPLICATE_VALUE_IN_PRODUCT_MODEL,
                [
                    '%values%' => '[blue]',
                    '%attributes%' => 'color',
                    '%validated_entity%' => 'entity_code',
                    '%sibling_with_same_value%' => 'sibling1',
                ]
            )
            ->willReturn($violation);
        $violation->atPath('attribute')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicate_value_in_any_sibling_variant_product(
        $context,
        $repository,
        $axesProvider,
        $uniqueAxesCombinationSet,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        ProductInterface $entity,
        ProductInterface $sibling1,
        ProductInterface $sibling2,
        AttributeInterface $color,
        ValueInterface $blue,
        ValueInterface $yellow,
        UniqueVariantAxis $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $entity->getIdentifier()->willReturn('entity_identifier');
        $entity->getParent()->willReturn($parent);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $axesProvider->getAxes($entity)->willReturn([$color]);
        $color->getCode()->willReturn('color');

        $repository->findSiblings($entity)->willReturn([$sibling1, $sibling2]);
        $sibling1->getIdentifier()->willReturn('sibling1');
        $sibling2->getIdentifier()->willReturn('sibling2');

        $entity->getValue('color')->willReturn($blue);
        $sibling1->getValue('color')->willReturn($blue);
        $sibling2->getValue('color')->willReturn($yellow);

        $blue->__toString()->willReturn('[blue]');
        $yellow->__toString()->willReturn('[yellow]');

        $uniqueAxesCombinationSet->addCombination($entity, '[blue]')->shouldBeCalled();

        $context
            ->buildViolation(
                UniqueVariantAxis::DUPLICATE_VALUE_IN_VARIANT_PRODUCT,
                [
                    '%values%' => '[blue]',
                    '%attributes%' => 'color',
                    '%validated_entity%' => 'entity_identifier',
                    '%sibling_with_same_value%' => 'sibling1',
                ]
            )
            ->willReturn($violation);
        $violation->atPath('attribute')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicate_value_in_any_sibling_product_model_with_multiple_attributes_in_axis(
        $context,
        $repository,
        $axesProvider,
        $uniqueAxesCombinationSet,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        ProductModelInterface $entity,
        ProductModelInterface $sibling1,
        ProductModelInterface $sibling2,
        AttributeInterface $color,
        ValueInterface $blue,
        ValueInterface $yellow,
        AttributeInterface $size,
        ValueInterface $xl,
        UniqueVariantAxis $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $entity->getCode()->willReturn('entity_code');
        $entity->getParent()->willReturn($parent);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $axesProvider->getAxes($entity)->willReturn([$color, $size]);
        $color->getCode()->willReturn('color');
        $size->getCode()->willReturn('size');

        $repository->findSiblings($entity)->willReturn([$sibling1, $sibling2]);
        $sibling1->getCode()->willReturn('sibling1');
        $sibling2->getCode()->willReturn('sibling2');

        $entity->getValue('color')->willReturn($blue);
        $entity->getValue('size')->willReturn($xl);

        $sibling1->getValue('color')->willReturn($blue);
        $sibling1->getValue('size')->willReturn($xl);

        $sibling2->getValue('color')->willReturn($yellow);
        $sibling2->getValue('size')->willReturn($xl);

        $blue->__toString()->willReturn('[blue]');
        $yellow->__toString()->willReturn('[yellow]');
        $xl->__toString()->willReturn('[xl]');

        $uniqueAxesCombinationSet->addCombination($entity, '[blue],[xl]')->shouldBeCalled();

        $context
            ->buildViolation(
                UniqueVariantAxis::DUPLICATE_VALUE_IN_PRODUCT_MODEL,
                [
                    '%values%' => '[blue],[xl]',
                    '%attributes%' => 'color,size',
                    '%validated_entity%' => 'entity_code',
                    '%sibling_with_same_value%' => 'sibling1',
                ]
            )
            ->willReturn($violation);
        $violation->atPath('attribute')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicate_value_in_any_sibling_variant_product_with_multiple_attributes_in_axis(
        $context,
        $repository,
        $axesProvider,
        $uniqueAxesCombinationSet,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        ProductInterface $entity,
        ProductInterface $sibling1,
        ProductInterface $sibling2,
        AttributeInterface $color,
        ValueInterface $blue,
        ValueInterface $yellow,
        AttributeInterface $size,
        ValueInterface $xl,
        UniqueVariantAxis $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $entity->getIdentifier()->willReturn('entity_identifier');
        $entity->getParent()->willReturn($parent);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $axesProvider->getAxes($entity)->willReturn([$color, $size]);
        $color->getCode()->willReturn('color');
        $size->getCode()->willReturn('size');

        $repository->findSiblings($entity)->willReturn([$sibling1, $sibling2]);
        $sibling1->getIdentifier()->willReturn('sibling1');
        $sibling2->getIdentifier()->willReturn('sibling2');

        $entity->getValue('color')->willReturn($blue);
        $entity->getValue('size')->willReturn($xl);

        $sibling1->getValue('color')->willReturn($blue);
        $sibling1->getValue('size')->willReturn($xl);

        $sibling2->getValue('color')->willReturn($yellow);
        $sibling2->getValue('size')->willReturn($xl);

        $blue->__toString()->willReturn('[blue]');
        $yellow->__toString()->willReturn('[yellow]');
        $xl->__toString()->willReturn('[xl]');

        $uniqueAxesCombinationSet->addCombination($entity, '[blue],[xl]')->shouldBeCalled();

        $context
            ->buildViolation(
                UniqueVariantAxis::DUPLICATE_VALUE_IN_VARIANT_PRODUCT,
                [
                    '%values%' => '[blue],[xl]',
                    '%attributes%' => 'color,size',
                    '%validated_entity%' => 'entity_identifier',
                    '%sibling_with_same_value%' => 'sibling1',
                ]
            )
            ->willReturn($violation);
        $violation->atPath('attribute')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicated_product_model_in_the_batch(
        $context,
        $repository,
        $axesProvider,
        $uniqueAxesCombinationSet,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $entity1,
        ProductModelInterface $entity2,
        ProductModelInterface $parent,
        AttributeInterface $color,
        ValueInterface $blue,
        AlreadyExistingAxisValueCombinationException $exception,
        UniqueVariantAxis $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $entity1->getParent()->willReturn($parent);
        $entity1->getFamilyVariant()->willReturn($familyVariant);
        $repository->findSiblings($entity1)->willReturn([]);
        $axesProvider->getAxes($entity1)->willReturn([$color]);
        $color->getCode()->willReturn('color');

        $entity2->getCode()->willReturn('entity_2');
        $entity2->getParent()->willReturn($parent);
        $entity2->getFamilyVariant()->willReturn($familyVariant);
        $repository->findSiblings($entity2)->willReturn([]);
        $axesProvider->getAxes($entity2)->willReturn([$color]);

        $entity1->getValue('color')->willReturn($blue);
        $entity2->getValue('color')->willReturn($blue);
        $blue->__toString()->willReturn('[blue]');

        $uniqueAxesCombinationSet->addCombination($entity2, '[blue]')->willThrow($exception->getWrappedObject());
        $exception->getEntityIdentifier()->willReturn('entity_1');

        $context
            ->buildViolation(
                UniqueVariantAxis::DUPLICATE_VALUE_IN_PRODUCT_MODEL,
                [
                    '%values%' => '[blue]',
                    '%attributes%' => 'color',
                    '%validated_entity%' => 'entity_2',
                    '%sibling_with_same_value%' => 'entity_1',
                ]
            )
            ->willReturn($violation);
        $violation->atPath('attribute')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity2, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicated_variant_product_in_the_batch(
        $context,
        $repository,
        $axesProvider,
        $uniqueAxesCombinationSet,
        FamilyVariantInterface $familyVariant,
        ProductInterface $entity1,
        ProductInterface $entity2,
        ProductModelInterface $parent,
        AttributeInterface $color,
        ValueInterface $blue,
        AlreadyExistingAxisValueCombinationException $exception,
        UniqueVariantAxis $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $entity1->getParent()->willReturn($parent);
        $entity1->getFamilyVariant()->willReturn($familyVariant);
        $repository->findSiblings($entity1)->willReturn([]);
        $axesProvider->getAxes($entity1)->willReturn([$color]);
        $color->getCode()->willReturn('color');

        $entity2->getIdentifier()->willReturn('entity_2');
        $entity2->getParent()->willReturn($parent);
        $entity2->getFamilyVariant()->willReturn($familyVariant);
        $repository->findSiblings($entity2)->willReturn([]);
        $axesProvider->getAxes($entity2)->willReturn([$color]);

        $entity1->getValue('color')->willReturn($blue);
        $entity2->getValue('color')->willReturn($blue);
        $blue->__toString()->willReturn('[blue]');

        $uniqueAxesCombinationSet->addCombination($entity2, '[blue]')->willThrow($exception->getWrappedObject());
        $exception->getEntityIdentifier()->willReturn('entity_1');

        $context
            ->buildViolation(
                UniqueVariantAxis::DUPLICATE_VALUE_IN_VARIANT_PRODUCT,
                [
                    '%values%' => '[blue]',
                    '%attributes%' => 'color',
                    '%validated_entity%' => 'entity_2',
                    '%sibling_with_same_value%' => 'entity_1',
                ]
            )
            ->willReturn($violation);
        $violation->atPath('attribute')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity2, $constraint);
    }
}
