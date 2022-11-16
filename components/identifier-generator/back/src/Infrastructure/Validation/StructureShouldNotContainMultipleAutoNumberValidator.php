<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StructureShouldNotContainMultipleAutoNumberValidator extends ConstraintValidator
{
    public function validate($structure, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, StructureShouldNotContainMultipleAutoNumber::class);
        if (!\is_array($structure)) {
            return;
        }

        $countAutonumber = 0;
        foreach ($structure as $property) {
            if (!\is_array($property)) {
                return;
            }
            if (!\array_key_exists('type', $property)) {
                return;
            }
            if (AutoNumber::type() === $property['type']) {
                ++$countAutonumber;
            }
        }

        if ($countAutonumber > StructureShouldNotContainMultipleAutoNumber::LIMIT_PER_STRUCTURE) {
            $this->context
                ->buildViolation($constraint->message, [
                    '{{limit}}' => StructureShouldNotContainMultipleAutoNumber::LIMIT_PER_STRUCTURE,
                    '{{type}}' => AutoNumber::type(),
                ])
                ->addViolation();
        }
    }
}
