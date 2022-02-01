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

    public function validate($valueUserIntent, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, LocaleAndChannelConsistency::class);
        Assert::isInstanceOf($valueUserIntent, ValueUserIntent::class);

        $attribute = $this->getAttributes->forCode($valueUserIntent->attributeCode());
        if (null === $attribute) {
            return;
        }

        $this->validateChannelCode($attribute, $valueUserIntent->channelCode());
        $this->validateLocaleCode($attribute, $valueUserIntent->localeCode(), $valueUserIntent->channelCode());
    }

    private function validateChannelCode(Attribute $attribute, ?string $channelCode): void
    {
        if (!$attribute->isScopable()) {
            if (null !== $channelCode) {
                $this->addViolation(
                    LocaleAndChannelConsistency::CHANNEL_CODE_PROVIDED_FOR_NON_SCOPABLE_ATTRIBUTE,
                    [
                        '{{ attributeCode }}' => $attribute->code(),
                        '{{ channelCode }}' => $channelCode,
                    ]
                );
            }

            return;
        }

        if (null === $channelCode) {
            $this->addViolation(
                LocaleAndChannelConsistency::NO_CHANNEL_CODE_PROVIDED_FOR_SCOPABLE_ATTRIBUTE,
                [
                    '{{ attributeCode }}' => $attribute->code(),
                ]
            );

            return;
        }
        if (!$this->channelExistsWithLocale->doesChannelExist($channelCode)) {
            $this->addViolation(
                LocaleAndChannelConsistency::CHANNEL_DOES_NOT_EXIST,
                [
                    '{{ channelCode }}' => $channelCode,
                ]
            );
        }
    }

    private function validateLocaleCode(Attribute $attribute, ?string $localeCode, ?string $channelCode): void
    {
        if (!$attribute->isLocalizable()) {
            if (null !== $localeCode) {
                $this->addViolation(
                    LocaleAndChannelConsistency::LOCALE_CODE_PROVIDED_FOR_NON_LOCALIZABLE_ATTRIBUTE,
                    [
                        '{{ attributeCode }}' => $attribute->code(),
                        '{{ localeCode }}' => $localeCode,
                    ]
                );
            }

            return;
        }

        if (null === $localeCode) {
            $this->addViolation(
                LocaleAndChannelConsistency::NO_LOCALE_CODE_PROVIDED_FOR_LOCALIZABLE_ATTRIBUTE,
                [
                    '{{ attributeCode }}' => $attribute->code(),
                ]
            );

            return;
        }

        if (!$attribute->isScopable() && !$this->channelExistsWithLocale->isLocaleActive($localeCode)) {
            $this->addViolation(
                LocaleAndChannelConsistency::LOCALE_IS_NOT_ACTIVE,
                [
                    '{{ localeCode }}' => $localeCode,
                ]
            );

            return;
        }

        if ($attribute->isScopable() && null !== $channelCode &&
            $this->channelExistsWithLocale->doesChannelExist($channelCode) &&
            !$this->channelExistsWithLocale->isLocaleBoundToChannel($localeCode, $channelCode)
        ) {
            $this->addViolation(
                LocaleAndChannelConsistency::LOCALE_NOT_BOUND_TO_CHANNEL,
                [
                    '{{ localeCode }}' => $localeCode,
                    '{{ channelCode }}' => $channelCode,
                ]
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
                ]
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
