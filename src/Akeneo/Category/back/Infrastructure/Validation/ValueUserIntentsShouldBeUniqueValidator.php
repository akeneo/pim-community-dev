<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Validation;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Api\Command\UserIntents\ValueUserIntent;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ValueUserIntentsShouldBeUniqueValidator extends ConstraintValidator
{
    /**
     * @param array<UserIntent> $value
     */
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ValueUserIntentsShouldBeUnique::class);
        Assert::isArray($value);
        Assert::allImplementsInterface($value, UserIntent::class);

        $this->validUniqueConstraint($value, $constraint);
    }

    /**
     * @param UserIntent[] $valueUserIntents
     */
    private function validUniqueConstraint(array $valueUserIntents, Constraint $constraint): void
    {
        /** @var ValueUserIntent[] $localizeUserIntents */
        $valueUserIntents = array_values(array_filter($valueUserIntents, function ($userIntent) {
            return is_subclass_of($userIntent, ValueUserIntent::class)
                && $userIntent->channelCode() !== null && $userIntent->localeCode() !== null;
        }));

        $existingIntents = [];
        foreach ($valueUserIntents as $valueUserIntent) {
            $className = get_class($valueUserIntent);
            $identifier = $valueUserIntent->attributeCode().AbstractValue::SEPARATOR.$valueUserIntent->attributeUuid();
            $intentAttributeCode = $valueUserIntent->attributeCode();
            $intentChannel = $valueUserIntent->channelCode() ?? '<all_channels>';
            $intentLocale = $valueUserIntent->localeCode() ?? '<all_locales>';

            if (\in_array($intentAttributeCode, $existingIntents[$className][$identifier][$intentChannel][$intentLocale] ?? [])) {
                $this->context
                    ->buildViolation($constraint->message, ['{{ attributeCode }}' => $intentAttributeCode])
                    ->addViolation();
            } else {
                $existingIntents[$className][$identifier][$intentChannel][$intentLocale][] = $intentAttributeCode;
            }
        }
    }
}
