<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation;

use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\AttributeAsMainMedia;
use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\GetAttributeAsMainMediaInterface;
use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
final class IsValidAssetAttributeValidator extends ConstraintValidator
{
    private GetAttributeAsMainMediaInterface $getAttributeAsMainMedia;
    private ChannelExistsWithLocaleInterface $channelExistsWithLocale;

    public function __construct(
        GetAttributeAsMainMediaInterface $getAttributeAsMainMedia,
        ChannelExistsWithLocaleInterface $channelExistsWithLocale
    ) {
        $this->getAttributeAsMainMedia = $getAttributeAsMainMedia;
        $this->channelExistsWithLocale = $channelExistsWithLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        Assert::isInstanceOf($constraint, IsValidAttribute::class);
        $assetFamilyIdentifier = $value['asset_family_identifier'] ?? null;
        if (null === $assetFamilyIdentifier || !is_string($assetFamilyIdentifier)) {
            return;
        }

        $attributeAsMainMediaAsMainMedia = $this->getAttributeAsMainMedia->forAssetFamilyIdentifier($assetFamilyIdentifier);
        $localeCode = $value['locale'] ?? null;
        $channelCode = $value['channel'] ?? null;

        $this->validateChannel($attributeAsMainMediaAsMainMedia, $assetFamilyIdentifier, $channelCode);
        $this->validateLocale($attributeAsMainMediaAsMainMedia, $assetFamilyIdentifier, $channelCode, $localeCode);
    }

    /**
     * Check if channel data is consistent with the attribute is scopable property
     */
    private function validateChannel(
        AttributeAsMainMedia $attributeAsMainMedia,
        string $assetFamilyIdentifier,
        ?string $channel
    ): void {
        if ($attributeAsMainMedia->isScopable()) {
            $this->validateScopableAttribute($assetFamilyIdentifier, $channel);

            return;
        }

        if (null !== $channel) {
            $this->context->buildViolation(
                'akeneo.tailored_export.validation.attribute.channel_should_be_blank',
                [
                    '{{ attribute_code }}' => $assetFamilyIdentifier,
                ],
            )->atPath('[channel]')->addViolation();
        }
    }

    /**
     * Check if locale data is consistent with the attribute localizable property
     */
    private function validateLocale(
        AttributeAsMainMedia $attributeAsMainMedia,
        string $assetFamilyIdentifier,
        ?string $channel,
        ?string $localeCode
    ): void {
        if (!$attributeAsMainMedia->isLocalizable() && null !== $localeCode) {
            $this->context->buildViolation(
                'akeneo.tailored_export.validation.attribute.locale_should_be_blank',
                [
                    '{{ attribute_code }}' => $assetFamilyIdentifier,
                ],
            )->atPath('[locale]')->addViolation();
        } elseif ($attributeAsMainMedia->isLocalizable()) {
            $this->validateLocalizableAttribute($attributeAsMainMedia, $assetFamilyIdentifier, $channel, $localeCode);
        }
    }

    private function validateScopableAttribute(string $assetFamilyIdentifier, ?string $channelCode): void
    {
        if (null === $channelCode) {
            $this->context->buildViolation(
                'akeneo.tailored_export.validation.attribute.channel_should_not_be_blank',
                [
                    '{{ attribute_code }}' => $assetFamilyIdentifier,
                ],
            )->addViolation();

            return;
        }

        if (!$this->channelExistsWithLocale->doesChannelExist($channelCode)) {
            $this->context->buildViolation(
                'akeneo.tailored_export.validation.channel.should_exist',
                [
                    '{{ channel_code }}' => $channelCode,
                ],
            )->atPath('[channel]')->addViolation();
        }
    }

    private function validateLocalizableAttribute(AttributeAsMainMedia $attributeAsMainMedia, string $assetFamilyIdentifier, ?string $channelCode, ?string $localeCode)
    {
        if (null === $localeCode) {
            $this->context->buildViolation(
                'akeneo.tailored_export.validation.attribute.locale_should_not_be_blank',
                [
                    '{{ attribute_code }}' => $assetFamilyIdentifier,
                ],
            )->addViolation();

            return;
        }

        if ($attributeAsMainMedia->isScopable() && !$this->channelExistsWithLocale->isLocaleBoundToChannel($localeCode, $channelCode)) {
            $this->context->buildViolation(
                'akeneo.tailored_export.validation.locale.should_be_bound_to_channel',
                [
                    '{{ locale_code }}' => $localeCode,
                    '{{ channel_code }}' => $channelCode,
                ],
            )->atPath('[locale]')->addViolation();
        }

        if (!$attributeAsMainMedia->isScopable() && !$this->channelExistsWithLocale->isLocaleActive($localeCode)) {
            $this->context->buildViolation(
                'akeneo.tailored_export.validation.locale.should_be_active',
                [
                    '{{ locale_code }}' => $localeCode,
                ],
            )->atPath('[locale]')->addViolation();
        }
    }
}
