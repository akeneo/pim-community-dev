<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Operation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\ConstraintValidator;

class MeasurementRoundingOperationValidator extends ConstraintValidator
{
    public function validate($operation, Constraint $constraint): void
    {
        if (!$constraint instanceof MeasurementRoundingOperationConstraint) {
            throw new \InvalidArgumentException('Invalid constraint');
        }

        $this->context->getValidator()
            ->inContext($this->context)
            ->validate($operation, new Collection([
                'fields' => [
                    'type' => new EqualTo(['value' => 'measurement_rounding']),
                    'rounding_type' => new EqualTo(['value' => 'standard']),
                    'precision' => new Optional(new Range(['min' => 0, 'max' => 12]))
                ]
            ]));
    }
}
