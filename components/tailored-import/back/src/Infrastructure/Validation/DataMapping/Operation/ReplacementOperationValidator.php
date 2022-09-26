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

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CategoriesReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\FamilyReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\MultiSelectReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SimpleReferenceEntityReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SimpleSelectReplacementOperation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;

class ReplacementOperationValidator extends ConstraintValidator
{
    public function validate($operation, Constraint $constraint): void
    {
        $validator = $this->context->getValidator();

        $validator->inContext($this->context)->validate($operation, new Collection([
            'fields' => [
                'uuid' => [new Uuid(), new NotBlank()],
                'type' => new Choice([
                    SimpleSelectReplacementOperation::TYPE,
                    MultiSelectReplacementOperation::TYPE,
                    CategoriesReplacementOperation::TYPE,
                    FamilyReplacementOperation::TYPE,
                    SimpleReferenceEntityReplacementOperation::TYPE
                ]),
                'mapping' => new All([
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
                ]),
            ],
        ]));

        $this->validateSourceValuesAreUnique($operation['mapping']);
    }

    public function validateSourceValuesAreUnique(array $mapping): void
    {
        $uniqueValues = [];

        foreach ($mapping as $code => $sourceValues) {
            if (!empty(array_intersect($sourceValues, $uniqueValues))) {
                $this->context->buildViolation(OperationConstraint::SOURCE_VALUES_SHOULD_BE_UNIQUE)
                    ->atPath(sprintf('[mapping][%s]', $code))
                    ->addViolation();
            }

            $uniqueValues = [...$uniqueValues, ...$sourceValues];
        }
    }
}
