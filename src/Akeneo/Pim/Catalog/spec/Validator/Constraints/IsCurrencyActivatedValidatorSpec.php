<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Validator\Constraints\IsCurrencyActivated;
use Pim\Component\Catalog\Validator\Constraints\IsCurrencyActivatedValidator;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsCurrencyActivatedValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IsCurrencyActivatedValidator::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(ConstraintValidator::class);
    }

    function it_does_not_add_violation_when_validating_null_value($context, IsCurrencyActivated $constraint)
    {
        $context->buildViolation()->shouldNotBeCalled();
        $this->validate(null, $constraint);
    }

    function it_does_not_add_violation_when_validating_something_else_than_a_currency(
        $context,
        IsCurrencyActivated $constraint
    ) {
        $context->buildViolation()->shouldNotBeCalled();

        $this->validate(new \stdClass(), $constraint);
    }

    function it_does_not_add_violation_when_validating_an_activated_currency(
        $context,
        CurrencyInterface $currency,
        IsCurrencyActivated $constraint
    ) {
        $currency->isActivated()->willReturn(true);
        $context->buildViolation()->shouldNotBeCalled();

        $this->validate($currency, $constraint);
    }

    function it_adds_violation_when_validating_an_inactivated_currency(
        $context,
        CurrencyInterface $currency,
        IsCurrencyActivated $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $currency->isActivated()->willReturn(false);
        $currency->getCode()->willReturn('CHF');

        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation
            ->setParameter('%currency%', 'CHF')
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->addViolation()->shouldBeCalled();

        $this->validate($currency, $constraint);
    }
}
