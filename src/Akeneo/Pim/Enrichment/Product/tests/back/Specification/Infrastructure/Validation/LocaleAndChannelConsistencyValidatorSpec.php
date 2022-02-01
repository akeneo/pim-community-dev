<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\LocaleAndChannelConsistency;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\LocaleAndChannelConsistencyValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LocaleAndChannelConsistencyValidatorSpec extends ObjectBehavior
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
        $this->shouldHaveType(LocaleAndChannelConsistencyValidator::class);
    }

    function it_throws_an_exception_for_a_wrong_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [
            new SetTextValue('name', 'en_US', 'ecommerce', 'My beautiful product'),
            new NotBlank(),
        ]);
    }

    function it_throws_an_exception_if_the_value_is_not_a_value_user_intent(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [
            new \stdClass(),
            new LocaleAndChannelConsistency(),
        ]);
    }

    function it_does_nothing_if_the_attribute_does_not_exist(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ): void {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn(null);
        $context->addViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            new SetTextValue('name', 'en_US', 'ecommerce', 'My beautiful product'),
            new LocaleAndChannelConsistency()
        );
    }

    function it_adds_a_violation_if_no_channel_code_is_provided_for_a_scopable_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn($this->getTextAttribute('name', true, false));

        $context
            ->buildViolation(
                LocaleAndChannelConsistency::NO_CHANNEL_CODE_PROVIDED_FOR_SCOPABLE_ATTRIBUTE,
                [
                    '{{ attributeCode }}' => 'name',
                ]
            )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            new SetTextValue('name', null, null, 'My beautiful product'),
            new LocaleAndChannelConsistency()
        );
    }

    function it_adds_a_violation_if_a_channel_code_is_provided_for_a_non_scopable_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn($this->getTextAttribute('name', false, false));

        $context
            ->buildViolation(
                LocaleAndChannelConsistency::CHANNEL_CODE_PROVIDED_FOR_NON_SCOPABLE_ATTRIBUTE,
                [
                    '{{ attributeCode }}' => 'name',
                    '{{ channelCode }}' => 'ecommerce',
                ]
            )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            new SetTextValue('name', null, 'ecommerce', 'My beautiful product'),
            new LocaleAndChannelConsistency()
        );
    }

    function it_adds_a_violation_if_the_channel_does_not_exist(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn($this->getTextAttribute('name', true, false));

        $context
            ->buildViolation(
                LocaleAndChannelConsistency::CHANNEL_DOES_NOT_EXIST,
                [
                    '{{ channelCode }}' => 'mobile',
                ]
            )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            new SetTextValue('name', null, 'mobile', 'My beautiful product'),
            new LocaleAndChannelConsistency()
        );
    }

    function it_adds_a_violation_if_no_locale_code_is_provided_for_a_localizable_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn($this->getTextAttribute('name', false, true));

        $context
            ->buildViolation(
                LocaleAndChannelConsistency::NO_LOCALE_CODE_PROVIDED_FOR_LOCALIZABLE_ATTRIBUTE,
                [
                    '{{ attributeCode }}' => 'name',
                ]
            )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            new SetTextValue('name', null, null, 'My beautiful product'),
            new LocaleAndChannelConsistency()
        );
    }

    function it_adds_a_violation_if_a_locale_code_is_provided_for_a_non_localizable_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn($this->getTextAttribute('name', false, false));

        $context
            ->buildViolation(
                LocaleAndChannelConsistency::LOCALE_CODE_PROVIDED_FOR_NON_LOCALIZABLE_ATTRIBUTE,
                [
                    '{{ attributeCode }}' => 'name',
                    '{{ localeCode }}' => 'en_US',
                ]
            )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            new SetTextValue('name', 'en_US', null, 'My beautiful product'),
            new LocaleAndChannelConsistency()
        );
    }

    function it_adds_a_violation_if_locale_code_is_not_active(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn($this->getTextAttribute('name', false, true));

        $context
            ->buildViolation(
                LocaleAndChannelConsistency::LOCALE_IS_NOT_ACTIVE,
                [
                    '{{ localeCode }}' => 'es_ES',
                ]
            )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            new SetTextValue('name', 'es_ES', null, 'My beautiful product'),
            new LocaleAndChannelConsistency()
        );
    }

    function it_adds_a_violation_if_locale_is_not_bound_to_the_channel(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn($this->getTextAttribute('name', true, true));

        $context
            ->buildViolation(
                LocaleAndChannelConsistency::LOCALE_NOT_BOUND_TO_CHANNEL,
                [
                    '{{ localeCode }}' => 'fr_FR',
                    '{{ channelCode }}' => 'ecommerce',
                ]
            )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            new SetTextValue('name', 'fr_FR', 'ecommerce', 'My beautiful product'),
            new LocaleAndChannelConsistency()
        );
    }

    function it_adds_a_violation_if_locale_is_invalid_for_a_locale_specific_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ): void {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn(
            $this->getTextAttribute('name', false, true, ['en_US'])
        );

        $context
            ->buildViolation(
                LocaleAndChannelConsistency::INVALID_LOCALE_CODE_FOR_LOCALE_SPECIFIC_ATTRIBUTE,
                [
                    '{{ attributeCode }}' => 'name',
                    '{{ localeCode }}' => 'fr_FR',
                    '{{ availableLocales }}' => 'en_US',
                ]
            )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            new SetTextValue('name', 'fr_FR', null, 'My beautiful product'),
            new LocaleAndChannelConsistency()
        );
    }

    function it_does_not_add_a_violation_when_scope_and_locale_are_consistent(
        GetAttributes $getAttributes,
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $context
    ): void {
        $getAttributes->forCode('scopable_localizable')->willReturn(
            $this->getTextAttribute('scopable_localizable', true, true)
        );
        $getAttributes->forCode('scopable')->willReturn(
            $this->getTextAttribute('scopable', true, false)
        );
        $getAttributes->forCode('localizable')->willReturn(
            $this->getTextAttribute('localizable', false, true)
        );
        $getAttributes->forCode('locale_specific')->willReturn(
            $this->getTextAttribute('locale_specific', false, true, ['en_US'])
        );
        $getAttributes->forCode('simple')->willReturn(
            $this->getTextAttribute('simple', false, false)
        );

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            new SetTextValue('scopable_localizable', 'en_US', 'ecommerce', 'My beautiful product'),
            new LocaleAndChannelConsistency()
        );
        $this->validate(
            new SetTextValue('scopable', null, 'ecommerce', 'My beautiful product'),
            new LocaleAndChannelConsistency()
        );
        $this->validate(
            new SetTextValue('localizable', 'fr_FR', null, 'My beautiful product'),
            new LocaleAndChannelConsistency()
        );
        $this->validate(
            new SetTextValue('locale_specific', 'en_US', null, 'My beautiful product'),
            new LocaleAndChannelConsistency()
        );
        $this->validate(
            new SetTextValue('simple', null, null, 'My beautiful product'),
            new LocaleAndChannelConsistency()
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
