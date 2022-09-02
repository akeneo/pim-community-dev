<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\MeasurementFamilyExists;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class MeasurementFamilyShouldExistValidator extends ConstraintValidator
{
    public function __construct(private MeasurementFamilyExists $measurementFamilyExists)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, MeasurementFamilyShouldExist::class);
        if (!is_string($value)) {
            return;
        }

        if (!$this->measurementFamilyExists->forCode($value)) {
            $this->context
                ->buildViolation($constraint->message, ['{{ measurement_family_code }}' => $value])
                ->addViolation();
        }
    }
}
