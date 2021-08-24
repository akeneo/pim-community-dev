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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Format\Format;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ColumnsValidator extends ConstraintValidator
{
    private const MAX_COLUMN_COUNT = 1000;
    private const TARGET_MAX_LENGTH = 255;

    public function validate($columns, Constraint $constraint)
    {
        if (empty($columns)) {
            return;
        }

        $validator = $this->context->getValidator();
        $violations = $validator->validate($columns, [
            new Type('array'),
            new Count([
                'max' => self::MAX_COLUMN_COUNT,
                'maxMessage' => 'akeneo.tailored_export.validation.columns.max_column_count_reached'
            ])
        ]);

        if (0 < $violations->count()) {
            foreach ($violations as $violation) {
                $this->context->buildViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                )
                    ->addViolation();
            }

            return;
        }

        $columnTargets = [];
        foreach ($columns as $column) {
            $this->validateColumn($validator, $column);

            if (isset($column['target'])) {
                if (in_array($column['target'], $columnTargets)) {
                    $this->context->buildViolation(Columns::TARGET_NAME_SHOULD_BE_UNIQUE)
                        ->atPath(sprintf('[%s][target]', $column['uuid']))
                        ->setInvalidValue($column['target'])
                        ->addViolation();
                } else {
                    $columnTargets[] = $column['target'];
                }
            }
        }
    }

    private function validateColumn(ValidatorInterface $validator, $column): void
    {
        $violations = $validator->validate($column, new Collection([
            'fields' => [
                'uuid' => [new Uuid(), new NotBlank()],
                'target' => [
                    new Type('string'),
                    new NotBlank(['message' => Columns::TARGET_SHOULD_NOT_BE_BLANK]),
                    new Length([
                        'max' => self::TARGET_MAX_LENGTH,
                        'maxMessage' => Columns::TARGET_MAX_LENGTH_REACHED
                    ])
                ],
                'sources' => new Sources(),
                'format' => new Format(),
            ],
        ]));

        foreach ($violations as $violation) {
            $this->context->buildViolation(
                $violation->getMessage(),
                $violation->getParameters()
            )
                ->atPath(sprintf('[%s]%s', $column['uuid'], $violation->getPropertyPath()))
                ->setInvalidValue($violation->getInvalidValue())
                ->addViolation();
        }
    }
}
