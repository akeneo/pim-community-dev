<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\Constraints\IsIdentifierUsableAsGridFilter;
use Pim\Component\Catalog\Validator\Constraints\IsIdentifierUsableAsGridFilterValidator;
use PhpSpec\ObjectBehavior;
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
        ConstraintViolationBuilderInterface $violation
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_identifier');
        $attribute->isUseableAsGridFilter()->willReturn(false);
        $attribute->getCode()->shouldBeCalled()->willReturn('foobar');

        $context->buildViolation($constraint->message)->shouldBeCalled()->willReturn($violation);
        $violation->setParameter('%code%', 'foobar')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_does_not_build_a_violation_for_identifier_usable_as_grid_filter(
        $context,
        AttributeInterface $attribute,
        IsIdentifierUsableAsGridFilter $constraint
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_identifier');
        $attribute->isUseableAsGridFilter()->willReturn(true);

        $attribute->getCode()->shouldNotBeCalled();
        $context->buildViolation($constraint->message)->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }
}
