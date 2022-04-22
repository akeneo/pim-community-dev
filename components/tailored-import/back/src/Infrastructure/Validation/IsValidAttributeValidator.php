<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class IsValidAttributeValidator extends ConstraintValidator
{
    public function __construct(
        private GetAttributes $getAttributes,
        private ChannelExistsWithLocaleInterface $channelExistsWithLocale,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsValidAttribute) {
            throw new UnexpectedTypeException($constraint, IsValidAttribute::class);
        }

        if (null === $value) {
            return;
        }

        $attributeCode = $value['code'] ?? null;
        if (!is_string($attributeCode)) {
            return;
        }

        $attribute = $this->getAttributes->forCode($attributeCode);
        if (!$attribute instanceof Attribute) {
            return;
        }

        $localeCode = $value['locale'] ?? null;
        $channelCode = $value['channel'] ?? null;

        $this->validateChannel($attribute, $channelCode);
        $this->validateLocale($attribute, $channelCode, $localeCode);
    }

    /**
     * Check if channel data is consistent with the attribute is scopable property.
     */
    private function validateChannel(Attribute $attribute, ?string $channel): void
    {
        if ($attribute->isScopable()) {
            $this->validateScopableAttribute($attribute, $channel);

            return;
        }

        if (null !== $channel) {
            $this->context->buildViolation(
                'akeneo.tailored_import.validation.attribute.channel_should_be_blank',
                [
                    '{{ attribute_code }}' => $attribute->code(),
                ],
            )->atPath('[channel]')->addViolation();
        }
    }

    /**
     * Check if locale data is consistent with the attribute localizable property.
     */
    private function validateLocale(Attribute $attribute, ?string $channel, ?string $localeCode): void
    {
        if (!$attribute->isLocalizable() && null !== $localeCode) {
            $this->context->buildViolation(
                'akeneo.tailored_import.validation.attribute.locale_should_be_blank',
                [
                    '{{ attribute_code }}' => $attribute->code(),
                ],
            )->atPath('[locale]')->addViolation();
        } elseif ($attribute->isLocalizable()) {
            $this->validateLocalizableAttribute($attribute, $channel, $localeCode);
        }

        if ($attribute->isLocaleSpecific() && null !== $localeCode && !in_array($localeCode, $attribute->availableLocaleCodes())) {
            $this->context->buildViolation(
                'akeneo.tailored_import.validation.attribute.invalid_specific_locale',
                [
                    '{{ attribute_code }}' => $attribute->code(),
                ],
            )->atPath('[locale]')->addViolation();
        }
    }

    private function validateScopableAttribute(Attribute $attribute, ?string $channelCode): void
    {
        if (null === $channelCode) {
            $this->context->buildViolation(
                'akeneo.tailored_import.validation.attribute.channel_should_not_be_blank',
                [
                    '{{ attribute_code }}' => $attribute->code(),
                ],
            )->atPath('[code]')->addViolation();

            return;
        }

        if (!$this->channelExistsWithLocale->doesChannelExist($channelCode)) {
            $this->context->buildViolation(
                'akeneo.tailored_import.validation.channel.should_exist',
                [
                    '{{ channel_code }}' => $channelCode,
                ],
            )->atPath('[channel]')->addViolation();
        }
    }

    private function validateLocalizableAttribute(Attribute $attribute, ?string $channelCode, ?string $localeCode): void
    {
        if (null === $localeCode) {
            $this->context->buildViolation(
                'akeneo.tailored_import.validation.attribute.locale_should_not_be_blank',
                [
                    '{{ attribute_code }}' => $attribute->code(),
                ],
            )->atPath('[code]')->addViolation();

            return;
        }

        if ($attribute->isScopable() && !$this->channelExistsWithLocale->isLocaleBoundToChannel($localeCode, $channelCode)) {
            $this->context->buildViolation(
                'akeneo.tailored_import.validation.locale.should_be_bound_to_channel',
                [
                    '{{ locale_code }}' => $localeCode,
                    '{{ channel_code }}' => $channelCode,
                ],
            )->atPath('[locale]')->addViolation();
        }

        if (!$attribute->isScopable() && !$this->channelExistsWithLocale->isLocaleActive($localeCode)) {
            $this->context->buildViolation(
                'akeneo.tailored_import.validation.locale.should_be_active',
                [
                    '{{ locale_code }}' => $localeCode,
                ],
            )->atPath('[locale]')->addViolation();
        }
    }
}
