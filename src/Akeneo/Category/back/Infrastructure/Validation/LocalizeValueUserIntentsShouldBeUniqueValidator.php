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
final class LocalizeValueUserIntentsShouldBeUniqueValidator extends ConstraintValidator
{

    /**
     * @param array<UserIntent> $value
     */
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, LocalizeValueUserIntentsShouldBeUnique::class);
        Assert::isArray($value);
        Assert::allImplementsInterface($value, UserIntent::class);

        $this->validUniqueConstraint($value, $constraint);
    }

    /**
     * @param UserIntent[] $value
     */
    private function validUniqueConstraint(array $value, Constraint $constraint): void
    {
        /** @var ValueUserIntent[] $localizeUserIntents */
        $localizeUserIntents = array_values(array_filter($value, function ($userIntent) {
            return is_subclass_of($userIntent, ValueUserIntent::class) && null !== $userIntent->localeCode();
        }));

        $existingIntents = [];
        foreach ($localizeUserIntents as $localizeIntent) {
            $className = get_class($localizeIntent);
            $identifier = $localizeIntent->attributeCode() . AbstractValue::SEPARATOR . $localizeIntent->attributeUuid();
            $intentLocale = $localizeIntent->localeCode() ?? '<all_locales>';

            if (\in_array($intentLocale, $existingIntents[$className][$identifier] ?? [])) {
                $this->context
                    ->buildViolation($constraint->message, ['{{ locale }}' => $intentLocale])
                    ->addViolation();
            } else {
                $existingIntents[$className][$identifier][] = $intentLocale;
            }
        }
    }
}
