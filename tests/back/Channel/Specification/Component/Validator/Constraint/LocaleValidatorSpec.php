<?php

namespace Specification\Akeneo\Channel\Component\Validator\Constraint;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Validator\Constraint\Locale;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LocaleValidatorSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $localeRepository, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($localeRepository);
        $this->initialize($context);
    }

    function it_validates_if_locale_exists(
        $localeRepository,
        $context,
        Locale $constraint,
        LocaleInterface $locale
    ) {
        $localeCode = 'foo';

        $localeRepository->findOneByIdentifier($localeCode)->willReturn($locale);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($localeCode, $constraint);
    }

    function it_adds_violation_if_locale_does_not_exist(
        $localeRepository,
        $context,
        Locale $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $localeCode = 'foo';

        $localeRepository->findOneByIdentifier($localeCode)->willReturn(null);
        $context->buildViolation($constraint->message, ['%locale%' => $localeCode])->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($localeCode, $constraint);
    }

    function it_does_not_validate_if_value_is_null($localeRepository, $context, Locale $constraint)
    {
        $localeCode = null;

        $localeRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($localeCode, $constraint);
    }
}
