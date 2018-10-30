<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotEmptyFamilyValidator;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotEmptyFamily;
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
        $this->shouldHaveType(NotEmptyFamilyValidator::class);
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
        $entity->isVariant()->willReturn(true);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_the_family_is_null(
        $context,
        ProductInterface $entity,
        NotEmptyFamily $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $entity->getFamily()->willReturn(null);
        $entity->getIdentifier()->willReturn('product_sku');
        $entity->isVariant()->willReturn(true);

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
