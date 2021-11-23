<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\ValidationShouldMatchColumnType;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\ValidationShouldMatchColumnTypeValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ValidationShouldMatchColumnTypeValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValidationShouldMatchColumnTypeValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_exception_with_the_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [['data_type' => 'text', 'code' => 'test'], new NotBlank()]
        );
    }

    function it_adds_a_violation_when_validation_does_not_match_column_type(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $context->buildViolation(
            Argument::type('string'),
            [
                '{{ expected }}' => 'max_length',
                '{{ given }}' => 'min',
                '{{ columnType }}' => 'text'
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);

        $violationBuilder->atPath('validations')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();
        $this->validate(
            ['data_type' => 'text', 'code' => 'test', 'validations' => ['min' => 0]],
            new ValidationShouldMatchColumnType()
        );
    }

    function it_adds_a_violation_when_column_type_does_not_allow_any_validation(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $context->buildViolation(Argument::type('string'), ['{{ given }}' => 'min', '{{ columnType }}' => 'boolean'])->shouldBeCalled()->willReturn(
            $violationBuilder
        );
        $violationBuilder->atPath('validations')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();
        $this->validate(
            ['data_type' => 'boolean', 'code' => 'test', 'validations' => ['min' => 0]],
            new ValidationShouldMatchColumnType()
        );
    }

    function it_does_not_add_a_violation_when_value_is_not_an_array(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(new NotBlank(), new ValidationShouldMatchColumnType());
    }

    function it_does_not_add_a_violation_when_data_type_is_not_defined(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(['validations' => ['min' => 10]], new ValidationShouldMatchColumnType());
    }

    function it_does_not_add_a_violation_when_data_type_is_unknown(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(['data_type' => 'unknown', 'validations' => ['min' => 10]], new ValidationShouldMatchColumnType());
    }

    function it_does_not_add_a_violation_when_validations_are_not_an_array(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(
            ['data_type' => 'text', 'validations' => new NotBlank()],
            new ValidationShouldMatchColumnType()
        );
    }

    function it_adds_only_one_violation_when_several_validations_do_not_match_column_type(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $context->buildViolation(
            Argument::type('string'),
            ['{{ expected }}' => 'max_length', '{{ given }}' => 'min, max, decimals_allowed', '{{ columnType }}' => 'text']
        )->shouldBeCalledOnce()->willReturn($violationBuilder);

        $violationBuilder->atPath('validations')->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();
        $this->validate(
            ['data_type' => 'text', 'code' => 'test', 'validations' => ['min' => 0, 'max' => 10, 'decimals_allowed' => true]],
            new ValidationShouldMatchColumnType()
        );
    }

}
