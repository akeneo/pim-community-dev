<?php

namespace Specification\Akeneo\Channel\Component\Validator\Constraint;

use Akeneo\Channel\Component\Validator\Constraint\LocaleCode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LocaleCodeValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_validates_a_valid_locale_code($context) {
        $constraint = new LocaleCode();

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('aa_AA', $constraint);
        $this->validate('aaa_AAA', $constraint);
        $this->validate('aaa_AAAA_AA', $constraint);
        $this->validate('aaa_aaa', $constraint);
        $this->validate('aaa_aaaa_aa', $constraint);
        $this->validate('aaa_aaaa_aa', $constraint);
        $this->validate('en_029', $constraint);
    }

    function it_add_a_violation_with_an_invalid_locale_code(ConstraintViolationBuilderInterface $violation, $context) {
        $constraint = new LocaleCode();
        $context->buildViolation($constraint->message)->willReturn($violation);

        $violation->addViolation()->shouldBeCalledTimes(8);

        $this->validate('aa', $constraint);
        $this->validate('aa_', $constraint);
        $this->validate('aa_a', $constraint);
        $this->validate('a_aa', $constraint);
        $this->validate('aA', $constraint);
        $this->validate('AA_', $constraint);
        $this->validate('aA_A', $constraint);
        $this->validate('A_AA', $constraint);
    }
}
