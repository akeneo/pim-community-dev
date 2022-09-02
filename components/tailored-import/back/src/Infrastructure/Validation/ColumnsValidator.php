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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ColumnsValidator extends ConstraintValidator
{
    private const MAX_COLUMN_COUNT = 500;
    private const LABEL_MAX_LENGTH = 255;
    private const MIN_INDEX = 1;

    public function validate($columns, Constraint $constraint): void
    {
        if (!$constraint instanceof Columns) {
            throw new UnexpectedTypeException($constraint, Columns::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($columns, [
            new Type('array'),
            new Count([
                'max' => self::MAX_COLUMN_COUNT,
                'maxMessage' => Columns::MAX_COUNT_REACHED,
            ]),
        ]);

        if (0 < $this->context->getViolations()->count() || empty($columns)) {
            return;
        }

        $this->validateColumnUuidsAreUnique($columns);

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        $this->validateColumnIndexesAreUnique($columns);

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        foreach ($columns as $column) {
            $this->validateColumn($validator, $column);
        }
    }

    private function validateColumnUuidsAreUnique(array $columns): void
    {
        $columnUuids = [];
        foreach ($columns as $column) {
            if (isset($column['uuid'])) {
                if (in_array($column['uuid'], $columnUuids)) {
                    $this->context->buildViolation(Columns::UUID_SHOULD_BE_UNIQUE)
                        ->atPath(sprintf('[%s][uuid]', $column['uuid']))
                        ->setInvalidValue($column['uuid'])
                        ->addViolation();
                } else {
                    $columnUuids[] = $column['uuid'];
                }
            }
        }
    }

    private function validateColumnIndexesAreUnique(array $columns): void
    {
        $columnIndexes = [];
        foreach ($columns as $column) {
            if (isset($column['index'])) {
                if (in_array($column['index'], $columnIndexes)) {
                    $this->context->buildViolation(Columns::INDEX_SHOULD_BE_UNIQUE)
                        ->atPath(sprintf('[%s][index]', $column['uuid']))
                        ->setInvalidValue($column['index'])
                        ->addViolation();
                } else {
                    $columnIndexes[] = $column['index'];
                }
            }
        }
    }

    private function validateColumn(ValidatorInterface $validator, array $column): void
    {
        $violations = $validator->validate($column, new Collection([
            'fields' => [
                'uuid' => [new Uuid(), new NotBlank()],
                'index' => [
                    new Type('integer'),
                    new Length(['min' => self::MIN_INDEX]),
                ],
                'label' => [
                    new Type('string'),
                    new NotBlank(['message' => Columns::LABEL_SHOULD_NOT_BE_BLANK]),
                    new Length([
                        'max' => self::LABEL_MAX_LENGTH,
                        'maxMessage' => Columns::LABEL_MAX_LENGTH_REACHED,
                    ]),
                ],
            ],
        ]));

        foreach ($violations as $violation) {
            $builder = $this->context->buildViolation(
                $violation->getMessage(),
                $violation->getParameters(),
            )
                ->atPath(sprintf('[%s]%s', $column['uuid'], $violation->getPropertyPath()))
                ->setInvalidValue($violation->getInvalidValue());
            if ($violation->getPlural()) {
                $builder->setPlural((int) $violation->getPlural());
            }
            $builder->addViolation();
        }
    }
}
