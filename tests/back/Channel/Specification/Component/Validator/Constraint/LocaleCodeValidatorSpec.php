<?php

namespace Specification\Akeneo\Channel\Component\Validator\Constraint;

use Akeneo\Channel\Component\Model\Locale;
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

    function it_validates_a_valid_locale_code($context, Locale $contextLocale) {
        $constraint = new LocaleCode();

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $context->getRoot()->willReturn($contextLocale);
        $contextLocale->getId()->willReturn(null);

        $this->validate('aa_AA', $constraint);
        $this->validate('aaa_AAA', $constraint);
        $this->validate('aaa_AAAA_AA', $constraint);
        $this->validate('aaa_aaa', $constraint);
        $this->validate('aaa_aaaa_aa', $constraint);
        $this->validate('aaa_aaaa_aa', $constraint);
        $this->validate('en_029', $constraint);
    }

    function it_add_a_violation_with_an_invalid_locale_code(ConstraintViolationBuilderInterface $violation, $context, Locale $contextLocale) {
        $constraint = new LocaleCode();
        $context->buildViolation($constraint->message)->willReturn($violation);
        $context->getRoot()->willReturn($contextLocale);
        $contextLocale->getId()->willReturn(null);

        $violation->addViolation()->shouldBeCalledTimes(10);

        $this->validate('aa', $constraint);
        $this->validate('aa_', $constraint);
        $this->validate('a_aa', $constraint);
        $this->validate('aA', $constraint);
        $this->validate('AA_', $constraint);
        $this->validate('aA_A', $constraint);
        $this->validate('A_AA', $constraint);
        $this->validate(' aa_AA', $constraint);
        $this->validate('aa_AA-test ', $constraint);
        $this->validate('aa AA ', $constraint);
    }

    function it_validates_an_old_pattern_if_locale_already_exists(ConstraintViolationBuilderInterface $violation, $context, Locale $contextLocale) {
        $constraint = new LocaleCode();

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $context->getRoot()->willReturn($contextLocale);
        $contextLocale->getId()->willReturn('fr_FR');

        $violation->addViolation()->shouldNotBeCalled();

        $this->validate('aa_AA-test', $constraint);
        $this->validate(' aa_AA', $constraint);
    }
}
