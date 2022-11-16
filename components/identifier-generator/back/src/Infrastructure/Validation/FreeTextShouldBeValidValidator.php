<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FreeTextShouldBeValidValidator extends ConstraintValidator
{
    public function validate($property, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, FreeTextShouldBeValid::class);
        if (!\is_array($property)) {
            return;
        }
        if (!\array_key_exists('type', $property)) {
            return;
        }
        if ($property['type'] !== FreeText::type()) {
            return;
        }

        if (!\array_key_exists('string', $property)) {
            $this->context
                ->buildViolation($constraint->stringKeyRequired)
                ->addViolation();
        }
    }
}
