<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
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

        $validScope = null;
        $validLocale = null;
        if ($attribute->isScopable()) {
            if (!\array_key_exists('scope', $condition)) {
                $this->context
                    ->buildViolation($constraint->scopeRequired, [
                        '{{ attributeCode }}' => $condition['attributeCode'],
                    ])
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
                } else {
                    $validScope = $condition['scope'];
                }
            }
        } else {
            if (\array_key_exists('scope', $condition)) {
                $this->context
                    ->buildViolation($constraint->notExpectedField)
                    ->atPath('[scope]')
                    ->addViolation();
            }
        }

        if ($attribute->isLocalizable()) {
            if (!\array_key_exists('locale', $condition)) {
                $this->context
                    ->buildViolation($constraint->localeRequired, [
                        '{{ attributeCode }}' => $condition['attributeCode'],
                    ])
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
                } else {
                    $validLocale = $condition['locale'];
                }
            }
        } else {
            if (\array_key_exists('locale', $condition)) {
                $this->context
                    ->buildViolation($constraint->notExpectedField)
                    ->atPath('[locale]')
                    ->addViolation();
            }
        }

        if ($validLocale
            && $validScope
            && !$this->isLocaleActivated($validScope, $validLocale)) {
            $this->context
                ->buildViolation($constraint->inactiveLocale, [
                    '{{ localeCode }}' => $validLocale,
                    '{{ scopeCode }}' => $validScope,
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
        foreach ($this->getChannelCodeWithLocaleCodes->findAll() as $channelCodesWithLocaleCodes) {
            if (\in_array($locale, $channelCodesWithLocaleCodes['localeCodes'])) {
                return true;
            }
        }

        return false;
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
