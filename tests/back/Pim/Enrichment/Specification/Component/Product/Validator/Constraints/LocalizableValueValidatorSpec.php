<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\LocalizableValue;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LocalizableValueValidatorSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $localeRepository, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($localeRepository);
        $this->initialize($context);
    }

    function it_does_not_validate_if_object_is_not_a_product_value(
        $context,
        LocalizableValue $constraint
    ) {
        $object = new \stdClass();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($object, $constraint);
    }

    function it_does_not_add_violations_if_value_is_localizable_and_has_an_existing_locale(
        $context,
        $localeRepository,
        ValueInterface $value,
        LocaleInterface $existingLocale,
        LocalizableValue $constraint
    ) {
        $value->isLocalizable()->willReturn(true);
        $value->getLocaleCode()->willReturn('en_US');
        $localeRepository->findOneByIdentifier('en_US')->willReturn($existingLocale);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    function it_adds_violations_if_value_is_localizable_and_does_not_have_locale(
        $context,
        ValueInterface $value,
        LocalizableValue $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $value->isLocalizable()->willReturn(true);
        $value->getLocaleCode()->willReturn(null);
        $value->getAttributeCode()->willReturn('attributeCode');

        $violationData = [
            '%attribute%' => 'attributeCode'
        ];
        $context->buildViolation($constraint->expectedLocaleMessage, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($value, $constraint);
    }

    function it_adds_violations_if_value_is_not_localizable_and_a_locale_is_provided(
        $context,
        ValueInterface $value,
        LocalizableValue $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $value->isLocalizable()->willReturn(false);
        $value->getLocaleCode()->willReturn('aLocale');
        $value->getAttributeCode()->willReturn('attributeCode');

        $violationData = [
            '%attribute%' => 'attributeCode'
        ];
        $context->buildViolation($constraint->unexpectedLocaleMessage, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($value, $constraint);
    }

    function it_adds_violations_if_value_is_localizable_and_its_locale_does_not_exist(
        $context,
        $localeRepository,
        ValueInterface $value,
        LocalizableValue $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $value->isLocalizable()->willReturn(true);
        $value->getLocaleCode()->willReturn('inexistingLocale');
        $value->getAttributeCode()->willReturn('attributeCode');
        $localeRepository->findOneByIdentifier('inexistingLocale')->willReturn(null);

        $violationData = [
            '%attribute%' => 'attributeCode',
            '%locale%'    => 'inexistingLocale'
        ];
        $context->buildViolation($constraint->inexistingLocaleMessage, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($value, $constraint);
    }
}
