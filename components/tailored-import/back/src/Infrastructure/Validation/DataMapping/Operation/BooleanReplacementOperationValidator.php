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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\BooleanReplacementOperation;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation\BooleanReplacementOperation as BooleanReplacementOperationConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class BooleanReplacementOperationValidator extends ConstraintValidator
{
    public function validate($operation, Constraint $constraint): void
    {
        if (!$constraint instanceof BooleanReplacementOperationConstraint) {
            throw new UnexpectedTypeException($constraint, BooleanReplacementOperationConstraint::class);
        }

        $fieldConstraint = [
            new NotBlank([
                'message' => OperationConstraint::REQUIRED,
            ]),
            new All([
                new NotBlank([
                    'message' => OperationConstraint::REQUIRED,
                ]),
                new Length([
                    'max' => 255,
                    'maxMessage' => OperationConstraint::MAX_LENGTH_REACHED,
                ]),
            ]),
        ];

        $this->context->getValidator()->inContext($this->context)->validate($operation, new Collection([
            'fields' => [
                'uuid' => [new Uuid(), new NotBlank()],
                'type' => new EqualTo(BooleanReplacementOperation::TYPE),
                'mapping' => new Collection([
                    'true' => $fieldConstraint,
                    'false' => $fieldConstraint,
                ]),
            ],
        ]));

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        $this->validateMappingValuesAreUnique($operation['mapping']);
    }

    public function validateMappingValuesAreUnique(array $mapping): void
    {
        $uniqueValues = [];

        foreach ($mapping as $code => $mappingValues) {
            if (!empty(array_intersect($mappingValues, $uniqueValues))) {
                $this->context->buildViolation(OperationConstraint::SOURCE_VALUES_SHOULD_BE_UNIQUE)
                    ->atPath(sprintf('[mapping][%s]', $code))
                    ->addViolation();
            }

            $uniqueValues = [...$uniqueValues, ...$mappingValues];
        }
    }
}
