<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\LocalizableValues;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\LocalizableValuesValidator;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LocalizableValuesValidatorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($localeRepository, $channelRepository, $getAttributes);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_is_a_localizable_values_validator()
    {
        $this->shouldBeAnInstanceOf(LocalizableValuesValidator::class);
    }

    function it_throws_an_exception_when_provided_with_an_invalid_constraint()
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [new WriteValueCollection(), new IsString()]);
    }

    function it_only_validates_value_collections(
        IdentifiableObjectRepositoryInterface $localeRepository,
        ExecutionContextInterface $context
    ) {
        $localeRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new LocalizableValues());
    }

    function it_only_validates_localizable_values(
        IdentifiableObjectRepositoryInterface $localeRepository,
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $localeRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $getAttributes->forCodes([])->willReturn([]);

        $values = new WriteValueCollection([
            ScalarValue::value('text', 'some text'),
            ScalarValue::scopableValue('number', 123, 'ecommerce'),
            ScalarValue::scopableValue('number', 456, 'mobile'),
        ]);

        $this->validate($values, new LocalizableValues());
    }

    function it_adds_a_violation_if_a_locale_does_not_exist(
        IdentifiableObjectRepositoryInterface $localeRepository,
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new LocalizableValues();
        $localeRepository->findOneByIdentifier('non_EXISTING')->willReturn(null);
        $values = new WriteValueCollection(
            [
                ScalarValue::localizableValue('localizable_text', 'Lorem ipsum', 'non_EXISTING')
            ]
        );

        $getAttributes->forCodes(['localizable_text'])->willReturn([
            'localizable_text' => new Attribute('localizable_text', AttributeTypes::TEXT, [], true, false, null, null, 'text', []),
        ]);

        $context->buildViolation($constraint->nonActiveLocaleMessage, [
            '%attribute_code%' => 'localizable_text',
            '%invalid_locale%' => 'non_EXISTING',
        ])->willReturn($violationBuilder);
        $violationBuilder->atPath('[localizable_text-<all_channels>-non_EXISTING]')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($values, $constraint);
    }

    function it_adds_a_violation_if_a_locale_is_not_activated(
        IdentifiableObjectRepositoryInterface $localeRepository,
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $esDO = (new Locale())->setCode('es_DO');
        $localeRepository->findOneByIdentifier('es_DO')->willReturn($esDO);

        $frFR = (new Locale())->setCode('fr_FR');
        $ecommerce = (new Channel())->setCode('ecommerce');
        $frFR->addChannel($ecommerce);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frFR);

        $values = new WriteValueCollection(
            [
                ScalarValue::localizableValue('localizable_text', 'Lorem ipsum', 'es_DO'),
            ]
        );

        $getAttributes->forCodes(['localizable_text'])->willReturn([
            'localizable_text' => new Attribute('localizable_text', AttributeTypes::TEXT, [], true, false, null, null, 'text', []),
        ]);

        $constraint = new LocalizableValues();
        $context->buildViolation(
            $constraint->nonActiveLocaleMessage,
            [
                '%attribute_code%' => 'localizable_text',
                '%invalid_locale%' => 'es_DO',
            ]
        )->willReturn($violationBuilder);
        $violationBuilder->atPath('[localizable_text-<all_channels>-es_DO]')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($values, $constraint);
    }

    function it_adds_a_violation_if_a_locale_is_not_bound_to_the_channel(
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $ecommerce = (new Channel())->setCode('ecommerce');
        $frFR = (new Locale())->setCode('fr_FR');
        $ecommerce->addLocale($frFR);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frFR);
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($ecommerce);
        $channelRepository->findOneByIdentifier('mobile')->willReturn(new Channel());

        $values = new WriteValueCollection(
            [
                ScalarValue::scopableLocalizableValue('scopable_localizable_text', 'Lorem ipsum', 'mobile', 'fr_FR'),
                ScalarValue::scopableLocalizableValue('scopable_localizable_text', 'some other text', 'ecommerce', 'fr_FR'),
            ]
        );

        $getAttributes->forCodes(['scopable_localizable_text'])->willReturn([
            'scopable_localizable_text' => new Attribute('scopable_localizable_text', AttributeTypes::TEXT, [], true, true, null, null, 'text', []),
        ]);

        $constraint = new LocalizableValues();
        $context->buildViolation(
            $constraint->invalidLocaleForChannelMessage,
            [
                '%attribute_code%' => 'scopable_localizable_text',
                '%channel_code%' => 'mobile',
                '%invalid_locale%' => 'fr_FR',
            ]
        )->willReturn($violationBuilder);
        $violationBuilder->atPath('[scopable_localizable_text-mobile-fr_FR]')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($values, $constraint);
    }

    function it_adds_a_violation_if_a_locale_is_not_part_of_the_available_locales_for_a_locale_specific_attribute(
        IdentifiableObjectRepositoryInterface $localeRepository,
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $ecommerce = (new Channel())->setCode('ecommerce');

        $frFR = (new Locale())->setCode('fr_FR');
        $frFR->addChannel($ecommerce);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frFR);

        $enUS = (new Locale())->setCode('en_US');
        $enUS->addChannel($ecommerce);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enUS);

        $getAttributes->forCodes(['name'])->willReturn([
            'name' => new Attribute('name', AttributeTypes::TEXT, [], true, false, null, null, 'text', ['en_US']),
        ]);

        $values = new WriteValueCollection(
            [
                ScalarValue::localizableValue('name', 'Product name', 'en_US'),
                ScalarValue::localizableValue('name', 'Nom du produit', 'fr_FR'),
            ]
        );

        $constraint = new LocalizableValues();
        $context->buildViolation(
            $constraint->invalidLocaleSpecificMessage,
            [
                '%attribute_code%' => 'name',
                '%invalid_locale%' => 'fr_FR',
            ]
        )->willReturn($violationBuilder);
        $violationBuilder->atPath('[name-<all_channels>-fr_FR]')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($values, $constraint);
    }
}
