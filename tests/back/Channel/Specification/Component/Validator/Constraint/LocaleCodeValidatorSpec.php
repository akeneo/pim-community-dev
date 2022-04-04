<?php

namespace Specification\Akeneo\Channel\Infrastructure\Component\Validator\Constraint;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\LocaleCode;
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

    function it_validates_a_valid_locale_code($context, LocaleInterface $contextLocale)
    {
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

        //Example from the docs (https://help.akeneo.com/pim/serenity/articles/manage-your-locales.html#how-to-add-a-new-locale)
        $this->validate('English_test_web', $constraint);
    }

    function it_adds_a_violation_with_an_invalid_locale_code(ConstraintViolationBuilderInterface $violation, $context, LocaleInterface $contextLocale)
    {
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

    function it_validates_an_old_pattern_if_locale_already_exists(ConstraintViolationBuilderInterface $violation, $context, LocaleInterface $contextLocale)
    {
        $constraint = new LocaleCode();

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $context->getRoot()->willReturn($contextLocale);
        $contextLocale->getId()->willReturn('fr_FR');

        $violation->addViolation()->shouldNotBeCalled();

        $this->validate('aa_AA-test', $constraint);
        $this->validate(' aa_AA', $constraint);
    }

    function it_adds_a_violation_with_an_invalid_locale_code_with_existing_locale(ConstraintViolationBuilderInterface $violation, $context, LocaleInterface $contextLocale)
    {
        $constraint = new LocaleCode();
        $context->buildViolation($constraint->message)->willReturn($violation);
        $context->getRoot()->willReturn($contextLocale);
        $contextLocale->getId()->willReturn('fr_FR');

        $violation->addViolation()->shouldBeCalledTimes(6);

        $this->validate('aa', $constraint);
        $this->validate('aa_', $constraint);
        $this->validate('a_aa', $constraint);
        $this->validate('aA', $constraint);
        $this->validate('AA_', $constraint);
        $this->validate('A_AA', $constraint);
    }
}
