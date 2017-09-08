<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\EntityWithFamilyVariantRepository;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Validator\Constraints\SiblingUniqueVariantAxes;
use Pim\Component\Catalog\Validator\UniqueAxesCombinationSet;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class SiblingUniqueVariantAxesValidatorSpec extends ObjectBehavior
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
        $this->shouldHaveType('Pim\Component\Catalog\Validator\Constraints\SiblingUniqueVariantAxesValidator');
    }

    function it_throws_an_exception_if_the_entity_is_not_supported(
        \DateTime $entity,
        SiblingUniqueVariantAxes $constraint
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
        SiblingUniqueVariantAxes $constraint
    ) {
        $entity->getFamilyVariant()->willReturn(null);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_the_entity_has_no_sibling(
        $context,
        $repository,
        $axesProvider,
        FamilyVariantInterface $familyVariant,
        EntityWithFamilyVariantInterface $entity,
        SiblingUniqueVariantAxes $constraint
    ) {
        $entity->getParent()->willReturn(null);
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
        EntityWithFamilyVariantInterface $entity,
        EntityWithFamilyVariantInterface $sibling,
        SiblingUniqueVariantAxes $constraint
    ) {
        $entity->getParent()->willReturn(null);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $repository->findSiblings($entity)->willReturn([$sibling]);
        $axesProvider->getAxes($entity)->willReturn([]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_there_is_no_duplicate_in_any_sibling_entity_from_database(
        $context,
        $repository,
        $axesProvider,
        $uniqueAxesCombinationSet,
        FamilyVariantInterface $familyVariant,
        EntityWithFamilyVariantInterface $entity,
        EntityWithFamilyVariantInterface $sibling1,
        EntityWithFamilyVariantInterface $sibling2,
        AttributeInterface $color,
        SiblingUniqueVariantAxes $constraint,
        ValueInterface $blue,
        ValueInterface $red,
        ValueInterface $yellow
    ) {
        $entity->getParent()->willReturn(null);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $repository->findSiblings($entity)->willReturn([$sibling1, $sibling2]);
        $axesProvider->getAxes($entity)->willReturn([$color]);
        $color->getCode()->willReturn('color');

        $entity->getValue('color')->willReturn($blue);
        $sibling1->getValue('color')->willReturn($red);
        $sibling2->getValue('color')->willReturn($yellow);

        $blue->__toString()->willReturn('[blue]');
        $red->__toString()->willReturn('[red]');
        $yellow->__toString()->willReturn('[yellow]');

        $uniqueAxesCombinationSet->addCombination($entity, Argument::any())->willReturn(true);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicate_value_in_any_sibling_entity(
        $context,
        $repository,
        $axesProvider,
        $uniqueAxesCombinationSet,
        FamilyVariantInterface $familyVariant,
        EntityWithFamilyVariantInterface $entity,
        EntityWithFamilyVariantInterface $sibling1,
        EntityWithFamilyVariantInterface $sibling2,
        AttributeInterface $color,
        SiblingUniqueVariantAxes $constraint,
        ValueInterface $blue,
        ValueInterface $yellow,
        ConstraintViolationBuilderInterface $violation
    ) {
        $entity->getParent()->willReturn(null);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $repository->findSiblings($entity)->willReturn([$sibling1, $sibling2]);
        $axesProvider->getAxes($entity)->willReturn([$color]);
        $color->getCode()->willReturn('color');

        $entity->getValue('color')->willReturn($blue);
        $sibling1->getValue('color')->willReturn($blue);
        $sibling2->getValue('color')->willReturn($yellow);

        $blue->__toString()->willReturn('[blue]');
        $yellow->__toString()->willReturn('[yellow]');

        $context
            ->buildViolation(
                SiblingUniqueVariantAxes::DUPLICATE_VALUE_IN_SIBLING, [
                    '%values%' => '[blue]',
                    '%attributes%' => 'color',
                ]
            )
            ->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $uniqueAxesCombinationSet->addCombination($entity, Argument::any())->willReturn(true);

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicate_value_in_any_sibling_entity_with_multiple_attributes_in_axis(
        $context,
        $repository,
        $axesProvider,
        $uniqueAxesCombinationSet,
        FamilyVariantInterface $familyVariant,
        EntityWithFamilyVariantInterface $entity,
        EntityWithFamilyVariantInterface $sibling1,
        EntityWithFamilyVariantInterface $sibling2,
        AttributeInterface $color,
        AttributeInterface $size,
        SiblingUniqueVariantAxes $constraint,
        ValueInterface $blue,
        ValueInterface $yellow,
        ValueInterface $xl,
        ConstraintViolationBuilderInterface $violation
    ) {
        $entity->getParent()->willReturn(null);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $repository->findSiblings($entity)->willReturn([$sibling1, $sibling2]);
        $axesProvider->getAxes($entity)->willReturn([$color, $size]);
        $color->getCode()->willReturn('color');
        $size->getCode()->willReturn('size');

        $entity->getValue('color')->willReturn($blue);
        $sibling1->getValue('color')->willReturn($blue);
        $sibling2->getValue('color')->willReturn($yellow);

        $entity->getValue('size')->willReturn($xl);
        $sibling1->getValue('size')->willReturn($xl);
        $sibling2->getValue('size')->willReturn($xl);

        $blue->__toString()->willReturn('[blue]');
        $yellow->__toString()->willReturn('[yellow]');
        $xl->__toString()->willReturn('[xl]');

        $context
            ->buildViolation(
                SiblingUniqueVariantAxes::DUPLICATE_VALUE_IN_SIBLING,
                [
                    '%values%' => '[blue],[xl]',
                    '%attributes%' => 'color,size',
                ]
            )
            ->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $uniqueAxesCombinationSet->addCombination($entity, Argument::any())->willReturn(true);

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicate_in_the_batch(
        $context,
        $repository,
        $axesProvider,
        $uniqueAxesCombinationSet,
        FamilyVariantInterface $familyVariant,
        EntityWithFamilyVariantInterface $entity1,
        EntityWithFamilyVariantInterface $entity2,
        ProductModelInterface $parent,
        AttributeInterface $color,
        SiblingUniqueVariantAxes $constraint,
        ValueInterface $blue,
        ConstraintViolationBuilderInterface $violation
    ) {
        $color->getCode()->willReturn('color');
        $blue->__toString()->willReturn('[blue]');

        $entity1->getParent()->willReturn($parent);
        $entity1->getFamilyVariant()->willReturn($familyVariant);
        $repository->findSiblings($entity1)->willReturn([]);
        $axesProvider->getAxes($entity1)->willReturn([$color]);
        $entity1->getValue('color')->willReturn($blue);

        $entity2->getParent()->willReturn($parent);
        $entity2->getFamilyVariant()->willReturn($familyVariant);
        $repository->findSiblings($entity2)->willReturn([]);
        $axesProvider->getAxes($entity2)->willReturn([$color]);
        $entity2->getValue('color')->willReturn($blue);

        $uniqueAxesCombinationSet->addCombination($entity1, '[blue]')->willReturn(true);
        $uniqueAxesCombinationSet->addCombination($entity2, '[blue]')->willReturn(false);

        $context
            ->buildViolation(
                SiblingUniqueVariantAxes::DUPLICATE_VALUE_IN_SIBLING,
                [
                    '%values%' => '[blue]',
                    '%attributes%' => 'color',
                ]
            )
            ->willReturn($violation);

        $this->validate($entity1, $constraint);
        $this->validate($entity2, $constraint);

        $violation->addViolation()->shouldHaveBeenCalled();
    }
}
