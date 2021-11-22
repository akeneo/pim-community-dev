<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\MaxShouldBeGreaterThanMin;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\MaxShouldBeGreaterThanMinValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class MaxShouldBeGreaterThanMinValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(MaxShouldBeGreaterThanMinValidator::class);
    }

    function it_should_throw_an_exception_with_the_wrong_constraint_type()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [['min' => 0, 'max' => 10], new NotBlank()]
        );
    }

    function it_should_add_a_violation_when_min_is_greater_than_max(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $context->buildViolation(Argument::cetera())->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();
        $this->validate(['min' => 5, 'max' => 0], new MaxShouldBeGreaterThanMin());
    }

    function it_should_not_add_a_violation_when_min_is_lower_than_max(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(['min' => 0, 'max' => 10], new MaxShouldBeGreaterThanMin());
    }

    function it_does_nothing_if_min_is_not_integer(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(['min' => '99', 'max' => 10], new MaxShouldBeGreaterThanMin());
    }

    function it_does_nothing_if_max_is_not_integer(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(['min' => 21, 'max' => '10'], new MaxShouldBeGreaterThanMin());
    }

    function it_does_nothing_if_validation_is_not_an_array(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate("not an array", new MaxShouldBeGreaterThanMin());
    }

    function it_does_nothing_if_min_or_max_is_not_defined(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(['min' => 5], new MaxShouldBeGreaterThanMin());
        $this->validate(['max' => 10], new MaxShouldBeGreaterThanMin());
        $this->validate([], new MaxShouldBeGreaterThanMin());
    }
}
