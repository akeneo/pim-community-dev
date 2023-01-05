<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ScopeAndLocaleShouldBeValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly GetAttributes $getAttributes,
        private readonly GetChannelCodeWithLocaleCodesInterface $getChannelCodeWithLocaleCodes,
        private readonly LocaleRepositoryInterface $localeRepository,
    ) {
    }

    public function validate($condition, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ScopeAndLocaleShouldBeValid::class);
        if (!\is_array($condition)) {
            return;
        }

        if (!\array_key_exists('attributeCode', $condition)) {
            return;
        }

        $attribute = $this->getAttributes->forCode($condition['attributeCode']);
        if (null === $attribute) {
            return;
        }

        if ($attribute->isScopable()) {
            if (!\array_key_exists('scope', $condition)) {
                $this->context
                    ->buildViolation($constraint->missingField)
                    ->atPath('[scope]')
                    ->addViolation();
            } else {
                if (!$this->scopeExists($condition['scope'])) {
                    $this->context
                        ->buildViolation($constraint->unknownScope, [
                            '{{ scopeCode }}' => $condition['scope'],
                        ])
                        ->atPath('[scope]')
                        ->addViolation();
                }
            }
        }

        if (!$attribute->isScopable() && \array_key_exists('scope', $condition)) {
            $this->context
                ->buildViolation($constraint->notExpectedField)
                ->atPath('[scope]')
                ->addViolation();
        }

        if ($attribute->isLocalizable()) {
            if (!\array_key_exists('locale', $condition)) {
                $this->context
                    ->buildViolation($constraint->missingField)
                    ->atPath('[locale]')
                    ->addViolation();
            } else {
                if (!$this->localeExists($condition['locale'])) {
                    $this->context
                        ->buildViolation($constraint->unknownLocale, [
                            '{{ localeCode }}' => $condition['locale'],
                        ])
                        ->atPath('[locale]')
                        ->addViolation();
                }
            }
        }

        if (!$attribute->isLocalizable() && \array_key_exists('locale', $condition)) {
            $this->context
                ->buildViolation($constraint->notExpectedField)
                ->atPath('[locale]')
                ->addViolation();
        }

        if ($attribute->isLocalizableAndScopable()
            && \array_key_exists('locale', $condition)
            && \array_key_exists('scope', $condition)
            && $this->scopeExists($condition['scope'])
            && $this->localeExists($condition['locale'])
            && !$this->isLocaleActivated($condition['scope'], $condition['locale'])) {
            $this->context
                ->buildViolation($constraint->inactiveLocale, [
                    '{{ localeCode }}' => $condition['locale'],
                    '{{ scopeCode }}' => $condition['scope'],
                ])
                ->atPath('[locale]')
                ->addViolation();
        }
    }

    private function scopeExists(string $scope): bool
    {
        foreach ($this->getChannelCodeWithLocaleCodes->findAll() as $channelCodesWithLocaleCodes) {
            if ($channelCodesWithLocaleCodes['channelCode'] === $scope) {
                return true;
            }
        }

        return false;
    }

    private function localeExists(string $locale): bool
    {
        $localeCodes = \array_map(fn (Locale $locale): string => $locale->getCode(), $this->localeRepository->getActivatedLocales());
        return \in_array($locale, $localeCodes);
    }

    private function isLocaleActivated(string $scope, string $locale): bool
    {
        foreach ($this->getChannelCodeWithLocaleCodes->findAll() as $channelCodesWithLocaleCodes) {
            if ($channelCodesWithLocaleCodes['channelCode'] === $scope) {
                return \in_array($locale, $channelCodesWithLocaleCodes['localeCodes']);
            }
        }

        return false;
    }
}
