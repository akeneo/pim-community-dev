<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\DuplicateOptions;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\DuplicateOptionsValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class DuplicateOptionsValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context): void
    {
        $this->initialize($context);
    }

    function it_is_a_constraint_validator(): void
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_is_a_duplicate_option_constraint_validator(): void
    {
        $this->shouldHaveType(DuplicateOptionsValidator::class);
    }

    function it_throws_an_exception_when_validating_anything_but_a_duplicate_options_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [['red'], new IsNull()]);
        $this->shouldNotThrow(\Exception::class)->during('validate', [['red'], new DuplicateOptions('colors')]);
    }

    function it_does_nothing_if_the_value_is_not_an_array(ExecutionContextinterface $context): void
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate('red', new DuplicateOptions('colors'));
    }

    function it_does_nothing_if_the_value_is_empty(ExecutionContextInterface $context): void
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate([], new DuplicateOptions('colors'));
    }

    function it_does_nothing_if_the_value_only_has_one_element(ExecutionContextInterface $context): void
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(['red'], new DuplicateOptions('colors'));
    }

    function it_throws_an_exception_if_one_of_the_values_is_not_a_string()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [['test', 1, null], new DuplicateOptions(['attributeCode' => 'colors'])]
        );
    }

    function it_does_not_build_a_violation_if_there_are_no_duplicates(ExecutionContextinterface $context): void
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(
            ['red', 'yellow'],
            new DuplicateOptions(['attributeCode' => 'colors'])
        );
    }

    function it_builds_a_violation_if_there_are_duplicate_options(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ): void {
        $value = ['red', 'yellow', 'red'];
        $constraint = new DuplicateOptions(['attributeCode' => 'colors']);

        $context->buildViolation(
            $constraint->message,
            [
                '{{ duplicate_options }}' => 'red',
                '%count%' => 1,
                '%attribute_code%' => 'colors',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setCode(DuplicateOptions::DUPLICATE_ATTRIBUTE_OPTIONS)
                         ->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    function it_builds_the_right_violation_if_an_option_appears_more_tha_twice(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ): void {
        $value = ['red', 'black', 'red', 'yellow', 'red', 'black', 'black'];
        $constraint = new DuplicateOptions(['attributeCode' => 'colors']);

        $context->buildViolation(
            $constraint->message,
            [
                '{{ duplicate_options }}' => 'red, black',
                '%count%' => 2,
                '%attribute_code%' => 'colors',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setCode(DuplicateOptions::DUPLICATE_ATTRIBUTE_OPTIONS)
                         ->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    function it_builds_an_exception_if_there_are_duplicate_options_with_a_different_case(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $value = ['red', 'yellow', 'Red', 'RED', 'black', 'YELLOW'];
        $constraint = new DuplicateOptions('colors');

        $context->buildViolation(
            $constraint->message,
            [
                '{{ duplicate_options }}' => 'Red, YELLOW',
                '%count%' => 2,
                '%attribute_code%' => 'colors',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setCode(DuplicateOptions::DUPLICATE_ATTRIBUTE_OPTIONS)
                         ->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }
}
