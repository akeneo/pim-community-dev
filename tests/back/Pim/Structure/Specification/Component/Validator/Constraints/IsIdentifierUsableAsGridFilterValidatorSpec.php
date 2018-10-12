<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IsIdentifierUsableAsGridFilter;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IsIdentifierUsableAsGridFilterValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsIdentifierUsableAsGridFilterValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IsIdentifierUsableAsGridFilterValidator::class);
    }

    function it_builds_a_violation_for_identifier_not_usable_as_grid_filter(
        $context,
        AttributeInterface $attribute,
        IsIdentifierUsableAsGridFilter $constraint,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $attribute->getType()->willReturn('pim_catalog_identifier');
        $attribute->isUseableAsGridFilter()->willReturn(false);
        $attribute->getCode()->shouldBeCalled()->willReturn('foobar');

        $context->buildViolation($constraint->message)->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setParameter('%code%', 'foobar')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('useableAsGridFilter')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_does_not_build_a_violation_for_identifier_usable_as_grid_filter(
        $context,
        AttributeInterface $attribute,
        IsIdentifierUsableAsGridFilter $constraint
    ) {
        $attribute->getType()->willReturn('pim_catalog_identifier');
        $attribute->isUseableAsGridFilter()->willReturn(true);

        $attribute->getCode()->shouldNotBeCalled();
        $context->buildViolation($constraint->message)->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }
}
