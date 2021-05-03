<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validation;
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

        $validator = Validation::createValidator();

        $violations = $validator->validate($columns, [
            new Type(['type' => 'array']),
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
            $this->validateColumn($validator, $column, $constraint);

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

    private function validateColumn(ValidatorInterface $validator, $column, Constraint $constraint): void
    {
        $violations = $validator->validate($column, [new Collection([
            'fields' => [
                'uuid' => [
                    new NotBlank(),
                    new Uuid()
                ],
                'target' => [
                    new Type([
                        'type' => 'string',
                    ]),
                    new NotBlank(['message' => Columns::TARGET_SHOULD_NOT_BE_BLANK]),
                    new Length([
                        'max' => self::TARGET_MAX_LENGTH,
                        'maxMessage' => Columns::TARGET_MAX_LENGTH_REACHED
                    ])
                ],
                'sources' => new Sources(),
                'format' => new Collection([
                    'type' => new Type(['type' => 'string']), //TODO use Enum concat
                    'elements' => [
                        new Type([
                            'type' => 'array',
                        ]),
                        new All([
                            'constraints' => [
                                new Collection([
                                    'fields' => [
                                        'type' => [
                                            new Type(['type' => 'string'])
                                        ],
                                        'value' => [
                                            new Type([
                                                'type' => 'string',
                                            ]),
                                        ],
                                    ],
                                ]),
                            ],
                        ]),
                    ]
                ])
            ],
        ])]);

        if (0 < $violations->count()) {
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
}
