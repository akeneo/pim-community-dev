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
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
final class IsValidAssetAttributeValidator extends ConstraintValidator
{
    public function __construct(
        private GetAttributeAsMainMediaInterface $getAttributeAsMainMedia,
        private ChannelExistsWithLocaleInterface $channelExistsWithLocale,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (!$constraint instanceof IsValidAssetAttribute) {
            throw new \InvalidArgumentException('Invalid constraint');
        }

        $assetFamilyCode = $constraint->assetFamilyCode;
        $attributeAsMainMediaAsMainMedia = $this->getAttributeAsMainMedia->forAssetFamilyCode($assetFamilyCode);
        $localeCode = $value['locale'] ?? null;
        $channelCode = $value['channel'] ?? null;

        $this->validateChannel($attributeAsMainMediaAsMainMedia, $assetFamilyCode, $channelCode);
        $this->validateLocale($attributeAsMainMediaAsMainMedia, $assetFamilyCode, $channelCode, $localeCode);
    }

    /**
     * Check if channel data is consistent with the attribute is scopable property.
     */
    private function validateChannel(
        AttributeAsMainMedia $attributeAsMainMedia,
        string $assetFamilyCode,
        ?string $channel,
    ): void {
        if ($attributeAsMainMedia->isScopable()) {
            $this->validateScopableAttribute($assetFamilyCode, $channel);

            return;
        }

        if (null !== $channel) {
            $this->context->buildViolation(
                'akeneo.tailored_export.validation.asset_collection.channel_should_be_blank',
                [
                    '{{ asset_family_code }}' => $assetFamilyCode,
                ],
            )->atPath('[channel]')->addViolation();
        }
    }

    /**
     * Check if locale data is consistent with the attribute localizable property.
     */
    private function validateLocale(
        AttributeAsMainMedia $attributeAsMainMedia,
        string $assetFamilyCode,
        ?string $channel,
        ?string $localeCode,
    ): void {
        if (!$attributeAsMainMedia->isLocalizable() && null !== $localeCode) {
            $this->context->buildViolation(
                'akeneo.tailored_export.validation.asset_collection.locale_should_be_blank',
                [
                    '{{ asset_family_code }}' => $assetFamilyCode,
                ],
            )->atPath('[locale]')->addViolation();
        } elseif ($attributeAsMainMedia->isLocalizable()) {
            $this->validateLocalizableAttribute($attributeAsMainMedia, $assetFamilyCode, $channel, $localeCode);
        }
    }

    private function validateScopableAttribute(string $assetFamilyCode, ?string $channelCode): void
    {
        if (null === $channelCode) {
            $this->context->buildViolation(
                'akeneo.tailored_export.validation.asset_collection.channel_should_not_be_blank',
                [
                    '{{ asset_family_code }}' => $assetFamilyCode,
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

    private function validateLocalizableAttribute(AttributeAsMainMedia $attributeAsMainMedia, string $assetFamilyCode, ?string $channelCode, ?string $localeCode): void
    {
        if (null === $localeCode) {
            $this->context->buildViolation(
                'akeneo.tailored_export.validation.asset_collection.locale_should_not_be_blank',
                [
                    '{{ asset_family_code }}' => $assetFamilyCode,
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
