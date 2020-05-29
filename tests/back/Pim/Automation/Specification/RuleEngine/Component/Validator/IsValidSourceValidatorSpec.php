<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\DefaultValueStringifier;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierRegistry;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\IsValidSource;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\IsValidSourceValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsValidSourceValidatorSpec extends ObjectBehavior
{
    function let(
        GetAttributes $getAttributes,
        ValueStringifierRegistry $valueStringifierRegistry,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($getAttributes, $valueStringifierRegistry);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(IsValidSourceValidator::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['foo', new IsNull()]);
    }

    function it_does_not_validate_a_non_string_value(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(456.23, new IsValidSource());
    }

    function it_does_nothing_if_the_attribute_does_not_exist(
        GetAttributes $getAttributes,
        ValueStringifierRegistry $valueStringifierRegistry,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode('foo')->shouldBeCalled()->willReturn(null);
        $valueStringifierRegistry->getStringifier(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('foo', new IsValidSource());
    }

    function it_does_not_build_a_violation_if_the_value_is_a_valid_source_attribute(
        GetAttributes $getAttributes,
        ValueStringifierRegistry $valueStringifierRegistry,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn(
            new Attribute('name', 'pim_catalog_text', [], false, false, null, null, null, 'string', [])
        );
        $valueStringifierRegistry->getStringifier('pim_catalog_text')->shouldBeCalled()->willReturn(
            new DefaultValueStringifier(['pim_catalog_text'])
        );
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('name', new IsValidSource());
    }

    function it_builds_a_violation_if_the_value_is_a_not_valid_source_attribute(
        GetAttributes $getAttributes,
        ValueStringifierRegistry $valueStringifierRegistry,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('image')->willReturn(
            new Attribute(
                'image', 'pim_catalog_image', [], false, false, null, null, null, 'file', []
            )
        );
        $valueStringifierRegistry->getStringifier('pim_catalog_image')->shouldBeCalled()->willReturn(null);

        $constraint = new IsValidSource();
        $context->buildViolation(
            $constraint->message,
            [
                '{{ field }}' => 'image',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate('image', $constraint);
    }
}
