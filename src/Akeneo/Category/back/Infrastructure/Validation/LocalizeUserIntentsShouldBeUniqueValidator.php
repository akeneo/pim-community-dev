<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Validation;

use Akeneo\Category\Api\Command\UserIntents\LocalizeUserIntent;
use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class LocalizeUserIntentsShouldBeUniqueValidator extends ConstraintValidator
{
    /**
     * @param array<UserIntent> $value
     */
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, LocalizeUserIntentsShouldBeUnique::class);
        Assert::isArray($value);
        Assert::allImplementsInterface($value, UserIntent::class);

        $this->validUniqueConstraint($value, $constraint);
    }

    /**
     * @param UserIntent[] $value
     */
    private function validUniqueConstraint(array $value, Constraint $constraint): void
    {
        /** @var LocalizeUserIntent[] $localizeUserIntents */
        $localizeUserIntents = array_values(array_filter($value, function ($userIntent) {
            return is_subclass_of($userIntent, LocalizeUserIntent::class);
        }));

        $existingIntents = [];
        foreach ($localizeUserIntents as $localizeIntent) {
            $className = get_class($localizeIntent);
            $intentLocale = $localizeIntent->localeCode() ?? '<all_locales>';

            if (\in_array($intentLocale, $existingIntents[$className] ?? [])) {
                $this->context
                    ->buildViolation($constraint->message, ['{{ locale }}' => $intentLocale])
                    ->addViolation();
            } else {
                $existingIntents[$className][] = $intentLocale;
            }
        }
    }
}
