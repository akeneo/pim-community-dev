<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LocaleAndChannelConsistencyValidator extends ConstraintValidator
{
    public function __construct(
        private GetAttributes $getAttributes,
        private ChannelExistsWithLocaleInterface $channelExistsWithLocale
    ) {
    }

    public function validate($valueUserIntents, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, LocaleAndChannelConsistency::class);
        Assert::isArray($valueUserIntents);
        Assert::allImplementsInterface($valueUserIntents, ValueUserIntent::class);

        $attributes = $this->getAttributes->forCodes(\array_map(
            static fn (ValueUserIntent $valueUserIntent): string => $valueUserIntent->attributeCode(),
            $valueUserIntents
        ));

        foreach ($valueUserIntents as $index => $valueUserIntent) {
            $attribute = $attributes[$valueUserIntent->attributeCode()] ?? null;
            if (null === $attribute) {
                return;
            }

            $this->validateChannelCode($attribute, $valueUserIntent->channelCode(), $index);
            $this->validateLocaleCode($attribute, $valueUserIntent->localeCode(), $valueUserIntent->channelCode(), $index);
        }
    }

    private function validateChannelCode(Attribute $attribute, ?string $channelCode, int $index): void
    {
        if (!$attribute->isScopable()) {
            if (null !== $channelCode) {
                $this->addViolation(
                    LocaleAndChannelConsistency::CHANNEL_CODE_PROVIDED_FOR_NON_SCOPABLE_ATTRIBUTE,
                    [
                        '{{ attributeCode }}' => $attribute->code(),
                        '{{ channelCode }}' => $channelCode,
                    ],
                    \sprintf('[%d].channelCode', $index)
                );
            }

            return;
        }

        if (null === $channelCode) {
            $this->addViolation(
                LocaleAndChannelConsistency::NO_CHANNEL_CODE_PROVIDED_FOR_SCOPABLE_ATTRIBUTE,
                [
                    '{{ attributeCode }}' => $attribute->code(),
                ],
                \sprintf('[%d].channelCode', $index)
            );

            return;
        }
        if (!$this->channelExistsWithLocale->doesChannelExist($channelCode)) {
            $this->addViolation(
                LocaleAndChannelConsistency::CHANNEL_DOES_NOT_EXIST,
                [
                    '{{ channelCode }}' => $channelCode,
                ],
                \sprintf('[%d].channelCode', $index)
            );
        }
    }

    private function validateLocaleCode(Attribute $attribute, ?string $localeCode, ?string $channelCode, int $index): void
    {
        if (!$attribute->isLocalizable()) {
            if (null !== $localeCode) {
                $this->addViolation(
                    LocaleAndChannelConsistency::LOCALE_CODE_PROVIDED_FOR_NON_LOCALIZABLE_ATTRIBUTE,
                    [
                        '{{ attributeCode }}' => $attribute->code(),
                        '{{ localeCode }}' => $localeCode,
                    ],
                    \sprintf('[%d].localeCode', $index)
                );
            }

            return;
        }

        if (null === $localeCode) {
            $this->addViolation(
                LocaleAndChannelConsistency::NO_LOCALE_CODE_PROVIDED_FOR_LOCALIZABLE_ATTRIBUTE,
                [
                    '{{ attributeCode }}' => $attribute->code(),
                ],
                \sprintf('[%d].localeCode', $index)
            );

            return;
        }

        if (!$attribute->isScopable() && !$this->channelExistsWithLocale->isLocaleActive($localeCode)) {
            $this->addViolation(
                LocaleAndChannelConsistency::LOCALE_IS_NOT_ACTIVE,
                [
                    '{{ localeCode }}' => $localeCode,
                ],
                \sprintf('[%d].localeCode', $index)
            );

            return;
        }

        if ($attribute->isScopable() && null !== $channelCode &&
            $this->channelExistsWithLocale->doesChannelExist($channelCode) &&
            !$this->channelExistsWithLocale->isLocaleBoundToChannel($localeCode, $channelCode)
        ) {
            $this->addViolation(
                LocaleAndChannelConsistency::LOCALE_NOT_ACTIVATED_FOR_CHANNEL,
                [
                    '{{ localeCode }}' => $localeCode,
                    '{{ channelCode }}' => $channelCode,
                ],
                \sprintf('[%d].localeCode', $index)
            );

            return;
        }

        if ($attribute->isLocaleSpecific() && !\in_array($localeCode, $attribute->availableLocaleCodes())) {
            $this->addViolation(
                LocaleAndChannelConsistency::INVALID_LOCALE_CODE_FOR_LOCALE_SPECIFIC_ATTRIBUTE,
                [
                    '{{ attributeCode }}' => $attribute->code(),
                    '{{ localeCode }}' => $localeCode,
                    '{{ availableLocales }}' => \implode(', ', $attribute->availableLocaleCodes()),
                ],
                \sprintf('[%d].localeCode', $index)
            );
        }
    }

    private function addViolation(string $message, array $messageParameters = [], ?string $path = null): void
    {
        $builder = $this->context->buildViolation($message, $messageParameters);
        if (null !== $path) {
            $builder->atPath($path);
        }
        $builder->addViolation();
    }
}
