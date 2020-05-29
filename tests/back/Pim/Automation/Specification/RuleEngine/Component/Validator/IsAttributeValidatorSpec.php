<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\IsAttribute;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\IsAttributeValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsAttributeValidatorSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($getAttributes);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(IsAttributeValidator::class);
    }

    function it_throws_an_exception_for_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['categories', new IsNull()]);
    }

    function it_does_not_validate_a_non_string_value(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new IsAttribute());
    }

    function it_does_not_add_a_violation_if_value_is_an_attribute_code(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn(
            new Attribute('name', 'pim_catalog_text', [], false, false, null, null, null, 'string', [])
        );
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('name', new IsAttribute());
    }

    function it_adds_a_violation_if_value_is_not_an_attribute_code(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $constraint = new IsAttribute();
        $getAttributes->forCode('foo')->shouldBeCalled()->willReturn(null);
        $context->buildViolation(
            $constraint->message,
            [
                '{{ code }}' => 'foo',
            ]
        )->shouldBeCalled()->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();

        $this->validate('foo', $constraint);
    }
}
