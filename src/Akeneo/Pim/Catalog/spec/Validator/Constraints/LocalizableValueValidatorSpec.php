<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Validator\Constraints\LocalizableValue;
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
        AttributeInterface $localizableAttribute,
        LocaleInterface $existingLocale,
        LocalizableValue $constraint
    ) {
        $value->getAttribute()->willReturn($localizableAttribute);
        $localizableAttribute->isLocalizable()->willReturn(true);
        $value->getLocale()->willReturn('mobile');
        $localeRepository->findOneByIdentifier('mobile')->willReturn($existingLocale);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    function it_adds_violations_if_value_is_localizable_and_does_not_have_locale(
        $context,
        ValueInterface $value,
        AttributeInterface $localizableAttribute,
        LocalizableValue $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $value->getAttribute()->willReturn($localizableAttribute);
        $localizableAttribute->isLocalizable()->willReturn(true);
        $value->getLocale()->willReturn(null);
        $localizableAttribute->getCode()->willReturn('attributeCode');

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
        AttributeInterface $notLocalizableAttribute,
        LocalizableValue $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $value->getAttribute()->willReturn($notLocalizableAttribute);
        $notLocalizableAttribute->isLocalizable()->willReturn(false);
        $value->getLocale()->willReturn('aLocale');
        $notLocalizableAttribute->getCode()->willReturn('attributeCode');

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
        AttributeInterface $localizableAttribute,
        LocalizableValue $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $value->getAttribute()->willReturn($localizableAttribute);
        $localizableAttribute->isLocalizable()->willReturn(true);
        $value->getLocale()->willReturn('inexistingLocale');
        $localizableAttribute->getCode()->willReturn('attributeCode');
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
