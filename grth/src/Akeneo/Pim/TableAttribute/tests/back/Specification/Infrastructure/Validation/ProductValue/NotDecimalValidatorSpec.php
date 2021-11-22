<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\NotDecimal;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\NotDecimalValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotDecimalValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(NotDecimalValidator::class);
    }

    function it_throws_an_exception_with_the_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [null, new NotBlank()]);
    }

    function it_adds_a_violation_if_provided_value_has_a_decimal_part(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new NotDecimal();
        $context->buildViolation($constraint->message, ['{{ invalid_value }}' => 3.1415927])
                ->shouldBeCalledOnce()
                ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate(3.1415927, new NotDecimal());
    }

    function it_does_nothing_if_value_is_not_numeric(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(['toto'], new NotDecimal());
        $this->validate(new \stdClass(), new NotDecimal());
        $this->validate('abc', new NotDecimal());
        $this->validate(null, new NotDecimal());
    }

    function it_does_not_add_a_violation_if_the_value_is_not_decimal(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(10, new NotDecimal());
        $this->validate('10', new NotDecimal());
        $this->validate('10.00', new NotDecimal());
    }
}
