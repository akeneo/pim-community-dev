<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\AreTableAttributeValidationsValid;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\AreTableAttributeValidationsValidValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AreTableAttributeValidationsValidValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContext $context)
    {
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(AreTableAttributeValidationsValidValidator::class);
    }

    function it_throws_an_exception_for_an_invalid_constraint(AttributeInterface $attribute)
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [$attribute, new NotBlank()]);
    }

    function it_does_not_validate_a_non_table_attribute(ExecutionContext $context, AttributeInterface $name)
    {
        $name->getType()->willReturn(AttributeTypes::TEXT);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($name, new AreTableAttributeValidationsValid());
    }

    function it_does_nothing_if_table_configuration_is_null(ExecutionContext $context, AttributeInterface $nutrition)
    {
        $nutrition->getType()->willReturn(AttributeTypes::TABLE);
        $nutrition->getRawTableConfiguration()->willReturn(null);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($nutrition, new AreTableAttributeValidationsValid());
    }

    function it_does_not_add_a_violation_if_validations_is_not_an_array(
        ExecutionContext $context,
        AttributeInterface $nutrition
    ) {
        $nutrition->getType()->willReturn(AttributeTypes::TABLE);
        $nutrition->getRawTableConfiguration()->willReturn([['validations' => 123]]);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($nutrition, new AreTableAttributeValidationsValid());
    }

    function it_adds_a_violation_in_case_of_unknown_validation(
        ExecutionContext $context,
        AttributeInterface $nutrition,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $nutrition->getType()->willReturn(AttributeTypes::TABLE);
        $nutrition->getRawTableConfiguration()->willReturn([['validations' => ['unknown' => 123]]]);
        $context->buildViolation(Argument::type('string'), [
            '{{ expected }}' => 'max_length',
            '{{ given }}' => 'unknown',
        ])->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('table_configuration[0].validations')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();
        $this->validate($nutrition, new AreTableAttributeValidationsValid());
    }

    function it_adds_a_violation_if_max_length_is_not_an_integer(
        ExecutionContext $context,
        AttributeInterface $nutrition,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $nutrition->getType()->willReturn(AttributeTypes::TABLE);
        $nutrition->getRawTableConfiguration()->willReturn([['validations' => ['max_length' => 'foo']]]);
        $context->buildViolation(
            Argument::type('string'),
            [
                '{{ expected }}' => 'integer',
                '{{ given }}' => 'string',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('table_configuration[0].validations.max_length')->shouldBeCalled()->willReturn(
            $violationBuilder
        );
        $violationBuilder->addViolation()->shouldBeCalled();
        $this->validate($nutrition, new AreTableAttributeValidationsValid());
    }

    function it_does_not_add_violation_if_validations_are_valid(
        ExecutionContext $context,
        AttributeInterface $nutrition
    ) {
        $nutrition->getType()->willReturn(AttributeTypes::TABLE);
        $nutrition->getRawTableConfiguration()->willReturn([['validations' => ['max_length' => 100]]]);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($nutrition, new AreTableAttributeValidationsValid());
    }

    function it_builds_a_violation_if_max_length_is_negative(
        ExecutionContext $context,
        AttributeInterface $nutrition,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $nutrition->getType()->willReturn(AttributeTypes::TABLE);
        $nutrition->getRawTableConfiguration()->willReturn([['validations' => ['max_length' => -100]]]);
        $context->buildViolation(
            Argument::type('string'),
            [
                '{{ given }}' => -100,
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('table_configuration[0].validations.max_length')->shouldBeCalled()->willReturn(
            $violationBuilder
        );
        $violationBuilder->addViolation()->shouldBeCalled();
        $this->validate($nutrition, new AreTableAttributeValidationsValid());
    }

    function it_builds_a_violation_if_max_length_is_zero(
        ExecutionContext $context,
        AttributeInterface $nutrition,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $nutrition->getType()->willReturn(AttributeTypes::TABLE);
        $nutrition->getRawTableConfiguration()->willReturn([['validations' => ['max_length' => 0]]]);
        $context->buildViolation(
            Argument::type('string'),
            [
                '{{ given }}' => 0,
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('table_configuration[0].validations.max_length')->shouldBeCalled()->willReturn(
            $violationBuilder
        );
        $violationBuilder->addViolation()->shouldBeCalled();
        $this->validate($nutrition, new AreTableAttributeValidationsValid());
    }
}
