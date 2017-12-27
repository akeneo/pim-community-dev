<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Validator\Constraints\NotEmptyFamily;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotEmptyFamilyValidatorSpec extends ObjectBehavior
{
    function let(
        ExecutionContextInterface $context
    ) {
        $this->initialize($context);
    }
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Validator\Constraints\NotEmptyFamilyValidator');
    }

    function it_throws_an_exception_if_the_entity_is_not_supported(
        \DateTime $entity,
        NotEmptyFamily $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$entity, $constraint]);
    }

    function it_throws_an_exception_if_the_constraint_is_not_supported(
        EntityWithFamilyVariantInterface $entity,
        Constraint $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$entity, $constraint]);
    }

    function it_raises_no_violation_if_the_entity_has_a_family(
        $context,
        ProductInterface $entity,
        FamilyInterface $family,
        NotEmptyFamily $constraint
    ) {
        $entity->getFamily()->willReturn($family);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_the_family_is_null(
        $context,
        ProductInterface $entity,
        FamilyInterface $family,
        NotEmptyFamily $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $entity->getFamily()->willReturn(null);
        $entity->getIdentifier()->willReturn('product_sku');

        $context
            ->buildViolation(
                NotEmptyFamily::MESSAGE, [
                    '%sku%' => 'product_sku'
                ]
            )
            ->willReturn($violation);
        $violation->atPath('family')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }
}
