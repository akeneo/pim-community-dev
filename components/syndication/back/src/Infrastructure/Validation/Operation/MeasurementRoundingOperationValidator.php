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

namespace Akeneo\Platform\Syndication\Infrastructure\Validation\Operation;

use Akeneo\Platform\Syndication\Application\Common\Operation\MeasurementRoundingOperation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;

class MeasurementRoundingOperationValidator extends ConstraintValidator
{
    private const PRECISION_NOT_BLANK_MESSAGE = 'akeneo.syndication.column_details.sources.operation.measurement_rounding.precision.validation.precision_should_not_be_blank';
    private const PRECISION_OUT_OF_RANGE_MESSAGE = 'akeneo.syndication.column_details.sources.operation.measurement_rounding.precision.validation.precision_is_out_of_range';

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
                    'rounding_type' => [new NotBlank(), new Choice(['choices' => MeasurementRoundingOperation::ROUNDING_TYPES])],
                    'precision' => [
                        new NotBlank(['message' => self::PRECISION_NOT_BLANK_MESSAGE]),
                        new Type('int'),
                        new Range(['min' => 0, 'max' => 12], notInRangeMessage: self::PRECISION_OUT_OF_RANGE_MESSAGE),
                    ]
                ]
            ]));
    }
}
