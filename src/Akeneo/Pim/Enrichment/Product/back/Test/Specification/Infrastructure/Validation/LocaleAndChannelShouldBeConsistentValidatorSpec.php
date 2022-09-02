<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\LocaleAndChannelShouldBeConsistent;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\LocaleAndChannelShouldBeConsistentValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LocaleAndChannelShouldBeConsistentValidatorSpec extends ObjectBehavior
{
    function let(
        GetAttributes $getAttributes,
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $context
    ): void {
        $channelExistsWithLocale->doesChannelExist('ecommerce')->willReturn(true);
        $channelExistsWithLocale->doesChannelExist('mobile')->willReturn(false);
        $channelExistsWithLocale->isLocaleActive('en_US')->willReturn(true);
        $channelExistsWithLocale->isLocaleActive('fr_FR')->willReturn(true);
        $channelExistsWithLocale->isLocaleActive('es_ES')->willReturn(false);
        $channelExistsWithLocale->isLocaleBoundToChannel('en_US', 'ecommerce')->willReturn(true);
        $channelExistsWithLocale->isLocaleBoundToChannel('fr_FR', 'ecommerce')->willReturn(false);

        $this->beConstructedWith($getAttributes, $channelExistsWithLocale);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator(): void
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(LocaleAndChannelShouldBeConsistentValidator::class);
    }

    function it_throws_an_exception_for_a_wrong_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [
            [new SetTextValue('name', 'ecommerce', 'en_US', 'My beautiful product')],
            new NotBlank(),
        ]);
    }

    function it_throws_an_exception_if_the_value_is_not_an_array(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [
            new SetTextValue('name', null, null, 'foo bar'),
            new LocaleAndChannelShouldBeConsistent(),
        ]);
    }

    function it_throws_an_exception_if_one_of_the_values_is_not_a_value_user_intent(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [
            [new SetTextValue('name', null, null, 'foo bar'), new \stdClass()],
            new LocaleAndChannelShouldBeConsistent(),
        ]);
    }

    function it_does_nothing_if_the_attribute_does_not_exist(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ): void {
        $getAttributes->forCodes(['name'])->shouldBeCalled()->willReturn(['name' => null]);
        $context->addViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            [new SetTextValue('name', 'ecommerce', 'en_US', 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    function it_adds_a_violation_if_no_channel_code_is_provided_for_a_scopable_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCodes(['name'])->shouldBeCalled()->willReturn([
            'name' => $this->getTextAttribute('name', true, false)
        ]);

        $context
            ->buildViolation(
                LocaleAndChannelShouldBeConsistent::NO_CHANNEL_CODE_PROVIDED_FOR_SCOPABLE_ATTRIBUTE,
                [
                    '{{ attributeCode }}' => 'name',
                ]
            )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('[0].channelCode')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            [new SetTextValue('name', null, null, 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    function it_adds_a_violation_if_a_channel_code_is_provided_for_a_non_scopable_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCodes(['name'])->shouldBeCalled()->willReturn([
            'name' => $this->getTextAttribute('name', false, false),
        ]);

        $context
            ->buildViolation(
                LocaleAndChannelShouldBeConsistent::CHANNEL_CODE_PROVIDED_FOR_NON_SCOPABLE_ATTRIBUTE,
                [
                    '{{ attributeCode }}' => 'name',
                    '{{ channelCode }}' => 'ecommerce',
                ]
            )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('[0].channelCode')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            [new SetTextValue('name', 'ecommerce', null , 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    function it_adds_a_violation_if_the_channel_does_not_exist(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCodes(['name'])->shouldBeCalled()->willReturn([
            'name' => $this->getTextAttribute('name', true, false),
        ]);

        $context
            ->buildViolation(
                LocaleAndChannelShouldBeConsistent::CHANNEL_DOES_NOT_EXIST,
                [
                    '{{ channelCode }}' => 'mobile',
                ]
            )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('[0].channelCode')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            [new SetTextValue('name', 'mobile', null, 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    function it_adds_a_violation_if_no_locale_code_is_provided_for_a_localizable_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCodes(['name'])->shouldBeCalled()->willReturn([
            'name' => $this->getTextAttribute('name', false, true),
        ]);

        $context
            ->buildViolation(
                LocaleAndChannelShouldBeConsistent::NO_LOCALE_CODE_PROVIDED_FOR_LOCALIZABLE_ATTRIBUTE,
                [
                    '{{ attributeCode }}' => 'name',
                ]
            )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('[0].localeCode')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            [new SetTextValue('name', null, null, 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    function it_adds_a_violation_if_a_locale_code_is_provided_for_a_non_localizable_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCodes(['name'])->shouldBeCalled()->willReturn([
            'name' => $this->getTextAttribute('name', false, false),
        ]);

        $context
            ->buildViolation(
                LocaleAndChannelShouldBeConsistent::LOCALE_CODE_PROVIDED_FOR_NON_LOCALIZABLE_ATTRIBUTE,
                [
                    '{{ attributeCode }}' => 'name',
                    '{{ localeCode }}' => 'en_US',
                ]
            )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('[0].localeCode')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            [new SetTextValue('name', null, 'en_US', 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    function it_adds_a_violation_if_locale_code_is_not_active(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCodes(['name'])->shouldBeCalled()->willReturn([
            'name' => $this->getTextAttribute('name', false, true),
        ]);

        $context
            ->buildViolation(
                LocaleAndChannelShouldBeConsistent::LOCALE_IS_NOT_ACTIVE,
                [
                    '{{ localeCode }}' => 'es_ES',
                ]
            )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('[0].localeCode')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            [new SetTextValue('name', null, 'es_ES', 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    function it_adds_a_violation_if_locale_is_not_bound_to_the_channel(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCodes(['name'])->shouldBeCalled()->willReturn([
            'name' => $this->getTextAttribute('name', true, true),
        ]);

        $context
            ->buildViolation(
                LocaleAndChannelShouldBeConsistent::LOCALE_NOT_ACTIVATED_FOR_CHANNEL,
                [
                    '{{ localeCode }}' => 'fr_FR',
                    '{{ channelCode }}' => 'ecommerce',
                ]
            )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('[0].localeCode')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            [new SetTextValue('name', 'ecommerce', 'fr_FR', 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    function it_adds_a_violation_if_locale_is_invalid_for_a_locale_specific_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ): void {
        $getAttributes->forCodes(['name'])->shouldBeCalled()->willReturn([
            'name' => $this->getTextAttribute('name', false, true, ['en_US']),
        ]);

        $context
            ->buildViolation(
                LocaleAndChannelShouldBeConsistent::INVALID_LOCALE_CODE_FOR_LOCALE_SPECIFIC_ATTRIBUTE,
                [
                    '{{ attributeCode }}' => 'name',
                    '{{ localeCode }}' => 'fr_FR',
                    '{{ availableLocales }}' => 'en_US',
                ]
            )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('[0].localeCode')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            [new SetTextValue('name', null, 'fr_FR', 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    function it_does_not_add_a_violation_when_scope_and_locale_are_consistent(
        GetAttributes $getAttributes,
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $context
    ): void {
        $getAttributes->forCodes([
            'scopable_localizable',
            'scopable',
            'localizable',
            'locale_specific',
            'simple',
        ])->shouldBeCalled()->willReturn([
            'localizable_scopable' => $this->getTextAttribute('scopable_localizable', true, true),
            'scopable' => $this->getTextAttribute('scopable', true, false),
            'localizable' => $this->getTextAttribute('localizable', false, true),
            'locale_specific' => $this->getTextAttribute('locale_specific', false, true, ['en_US']),
            'simple' => $this->getTextAttribute('simple', false, false),
        ]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            [
                new SetTextValue('scopable_localizable', 'ecommerce', 'en_US', 'My beautiful product'),
                new SetTextValue('scopable', 'ecommerce', null, 'My beautiful product'),
                new SetTextValue('localizable', null, 'fr_FR', 'My beautiful product'),
                new SetTextValue('locale_specific', null, 'en_US', 'My beautiful product'),
                new SetTextValue('simple', null, null, 'My beautiful product'),
            ],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    private function getTextAttribute(
        string $attributeCode,
        bool $scopable,
        bool $localizable,
        array $availableLocaleCodes = []
    ): Attribute {
        return new Attribute(
            $attributeCode,
            'pim_catalog_text',
            [],
            $localizable,
            $scopable,
            null,
            null,
            null,
            'text',
            $availableLocaleCodes
        );
    }
}
