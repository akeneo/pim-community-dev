<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Channel\Component\Query\FindActivatedCurrenciesInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\ActiveCurrencyValidator;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ActiveCurrency;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ActiveCurrencyValidatorSpec extends ObjectBehavior
{
    function let(
        FindActivatedCurrenciesInterface $findActivatedCurrencies,
        ExecutionContextInterface $context
    ) {
        $findActivatedCurrencies->forAllChannels()->willReturn(['EUR', 'USD']);

        $this->beConstructedWith($findActivatedCurrencies);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ActiveCurrencyValidator::class);
    }

    function it_throws_an_exception_for_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['EUR', new IsNull()]);
    }

    function it_does_not_validate_a_null_value(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(null, new ActiveCurrency());
    }

    function it_does_not_validate_a_non_string_value(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(12.5, new ActiveCurrency());
    }

    function it_does_not_build_violations_if_the_currency_is_active(ExecutionContextInterface $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('EUR', new ActiveCurrency());
    }

    function it_build_a_violation_if_currency_does_not_exist_or_is_not_active(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $constraint = new ActiveCurrency();
        $context->buildViolation($constraint->message, ['{{ currency }}' => 'UNKNOWN'])
            ->shouldBeCalled()->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();

        $this->validate('UNKNOWN', $constraint);
    }
}
