<?php

namespace Specification\Akeneo\Channel\Component\Validator\Constraint;

use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Validator\Constraint\ActivatedLocale;
use Akeneo\Channel\Component\Validator\Constraint\ActivatedLocaleValidator;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ActivatedLocaleValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context, IdentifiableObjectRepositoryInterface $localeRepository)
    {
        $this->beConstructedWith($localeRepository);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ActivatedLocaleValidator::class);
    }

    function it_adds_violation_if_locale_as_an_entity_not_activated(
        $context,
        $localeRepository,
        ActivatedLocale $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $locale = new Locale();
        $context->buildViolation(
            'The locale "%locale%" exists but has to be activated.',
            ['%locale%' => $locale]
        )->willReturn($violation);

        $violation->addViolation()->shouldBeCalled();
        $localeRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $this->validate($locale, $constraint)->shouldReturn(null);
    }

    function it_adds_violation_if_locale_as_a_string_is_not_activated(
        $context,
        $localeRepository,
        ActivatedLocale $constraint,
        ConstraintViolationBuilderInterface $violation,
        LocaleInterface $locale
    ) {
        $localeCode = 'en_US';
        $context->buildViolation(
            'The locale "%locale%" exists but has to be activated.',
            ['%locale%' => $locale]
        )->willReturn($violation);

        $violation->addViolation()->shouldBeCalled();
        $localeRepository->findOneByIdentifier($localeCode)->willReturn($locale);

        $this->validate($localeCode, $constraint)->shouldReturn(null);
    }

    function it_does_not_add_violation_if_locale_is_activated(
        $context,
        $localeRepository,
        ActivatedLocale $constraint,
        LocaleInterface $locale
    ) {
        $localeCode = 'en_US';
        $localeRepository->findOneByIdentifier($localeCode)->willReturn($locale);
        $locale->isActivated()->willReturn(true);

        $context->buildViolation(
            'The locale "%locale%" exists but has to be activated.',
            ['%locale%' => $locale]
        )->shouldNotBeCalled();

        $this->validate($localeCode, $constraint)->shouldReturn(null);
    }

    function it_does_not_add_violation_if_locale_is_null(
        $context,
        $localeRepository,
        ActivatedLocale $constraint,
        LocaleInterface $locale
    ) {
        $localeCode = null;
        $localeRepository->findOneByIdentifier($localeCode)->shouldNotBeCalled();
        $locale->isActivated()->shouldNotBeCalled();

        $context->buildViolation(
            'The locale "%locale%" exists but has to be activated.',
            ['%locale%' => $locale]
        )->shouldNotBeCalled();

        $this->validate($localeCode, $constraint)->shouldReturn(null);
    }

    function it_does_not_add_violation_if_locale_is_empty(
        $context,
        $localeRepository,
        ActivatedLocale $constraint,
        LocaleInterface $locale
    ) {
        $localeCode = '';
        $localeRepository->findOneByIdentifier($localeCode)->shouldNotBeCalled();
        $locale->isActivated()->shouldNotBeCalled();

        $context->buildViolation(
            'The locale "%locale%" exists but has to be activated.',
            ['%locale%' => $locale]
        )->shouldNotBeCalled();

        $this->validate($localeCode, $constraint)->shouldReturn(null);
    }

    function it_does_not_add_violation_if_locale_is_not_an_instance_of_locale_interface(
        $context,
        $localeRepository,
        ActivatedLocale $constraint,
        LocaleInterface $locale
    ) {
        $localeCode = new \stdClass();
        $localeRepository->findOneByIdentifier($localeCode)->shouldNotBeCalled();
        $locale->isActivated()->shouldNotBeCalled();

        $context->buildViolation(
            'The locale "%locale%" exists but has to be activated.',
            ['%locale%' => $locale]
        )->shouldNotBeCalled();

        $this->validate($localeCode, $constraint)->shouldReturn(null);
    }
}
