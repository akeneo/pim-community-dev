<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearPriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UserIntentsShouldBeUniqueValidator extends ConstraintValidator
{
    public function validate($valueUserIntents, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, UserIntentsShouldBeUnique::class);
        Assert::isArray($valueUserIntents);
        Assert::allImplementsInterface($valueUserIntents, ValueUserIntent::class);

        $existingIntents = [];
        $existingPriceIntents = [];

        /** @var ValueUserIntent $valueUserIntent */
        foreach ($valueUserIntents as $valueUserIntent) {
            $intentLocale = $valueUserIntent->localeCode() ?? '<all_locales>';
            $intentChannel = $valueUserIntent->channelCode() ?? '<all_channels>';
            $intentAttributeCode = $valueUserIntent->attributeCode();

            if ($valueUserIntent instanceof SetPriceValue || $valueUserIntent instanceof ClearPriceValue) {
                $currency = $valueUserIntent instanceof SetPriceValue
                    ? $valueUserIntent->priceValue()->currency()
                    : $valueUserIntent->currencyCode();
                if (\in_array($intentAttributeCode, $existingPriceIntents[$intentLocale][$intentChannel][$currency] ?? [])) {
                    $this->context
                        ->buildViolation($constraint->message, ['{{ attributeCode }}' => $intentAttributeCode])
                        ->addViolation();
                } else {
                    $existingPriceIntents[$intentLocale][$intentChannel][$currency][] = $intentAttributeCode;
                }
            } else {
                if (\in_array($intentAttributeCode, $existingIntents[$intentLocale][$intentChannel] ?? [])) {
                    $this->context
                        ->buildViolation($constraint->message, ['{{ attributeCode }}' => $intentAttributeCode])
                        ->addViolation();
                } else {
                    $existingIntents[$intentLocale][$intentChannel][] = $intentAttributeCode;
                }
            }
        }
    }
}
