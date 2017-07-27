<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\CanHaveVariantFamilyRepository;
use Pim\Component\Catalog\FamilyVariant\CanHaveFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CanHaveFamilyVariantInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Validator\Constraints\SiblingUniqueVariantAxes;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class SiblingUniqueVariantAxesValidatorSpec extends ObjectBehavior
{
    function let(
        CanHaveFamilyVariantAttributesProvider $axesProvider,
        CanHaveVariantFamilyRepository $repository,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($axesProvider, $repository);
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
        CanHaveFamilyVariantInterface $entity,
        Constraint $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$entity, $constraint]);
    }

    function it_raises_no_violation_if_the_entity_has_no_family_variant(
        $context,
        CanHaveFamilyVariantInterface $entity,
        SiblingUniqueVariantAxes $constraint
    ) {
        $entity->getFamilyVariant()->willReturn(null);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_the_entity_has_no_sibling(
        $context,
        $repository,
        FamilyVariantInterface $familyVariant,
        CanHaveFamilyVariantInterface $entity,
        SiblingUniqueVariantAxes $constraint
    ) {
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
        CanHaveFamilyVariantInterface $entity,
        CanHaveFamilyVariantInterface $sibling,
        SiblingUniqueVariantAxes $constraint
    ) {
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
        FamilyVariantInterface $familyVariant,
        CanHaveFamilyVariantInterface $entity,
        CanHaveFamilyVariantInterface $sibling1,
        CanHaveFamilyVariantInterface $sibling2,
        AttributeInterface $color,
        SiblingUniqueVariantAxes $constraint,
        ValueInterface $blue,
        ValueInterface $red,
        ValueInterface $yellow
    ) {
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

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicate_value_in_any_sibling_entity(
        $context,
        $repository,
        $axesProvider,
        FamilyVariantInterface $familyVariant,
        CanHaveFamilyVariantInterface $entity,
        CanHaveFamilyVariantInterface $sibling1,
        CanHaveFamilyVariantInterface $sibling2,
        AttributeInterface $color,
        SiblingUniqueVariantAxes $constraint,
        ValueInterface $blue,
        ValueInterface $yellow,
        ConstraintViolationBuilderInterface $violation
    ) {
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

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicate_value_in_any_sibling_entity_with_multiple_attributes_in_axis(
        $context,
        $repository,
        $axesProvider,
        FamilyVariantInterface $familyVariant,
        CanHaveFamilyVariantInterface $entity,
        CanHaveFamilyVariantInterface $sibling1,
        CanHaveFamilyVariantInterface $sibling2,
        AttributeInterface $color,
        AttributeInterface $size,
        SiblingUniqueVariantAxes $constraint,
        ValueInterface $blue,
        ValueInterface $yellow,
        ValueInterface $xl,
        ConstraintViolationBuilderInterface $violation
    ) {
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
                SiblingUniqueVariantAxes::DUPLICATE_VALUE_IN_SIBLING, [
                    '%values%' => '[blue],[xl]',
                    '%attributes%' => 'color,size',
                ]
            )
            ->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }
}
