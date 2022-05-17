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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;

class ReplacementOperationValidator extends ConstraintValidator
{
    public function validate($operation, Constraint $constraint): void
    {
        $validator = $this->context->getValidator();

        $violations = $validator->validate($operation, new Collection([
            'fields' => [
                'type' => new Choice([
                    // TODO replace with SimpleSelectReplacementOperation::TYPE when back is merged
                    'simple_select_replacement',
                ]),
                'mapping' => new All([
                    new NotBlank([
                        'message' => 'akeneo.tailored_import.validation.required',
                    ]),
                    new All([
                        new NotBlank([
                            'message' => 'akeneo.tailored_import.validation.required',
                        ]),
                        new Length([
                            'max' => 255,
                            'maxMessage' => 'akeneo.tailored_import.validation.max_length_reached',
                        ]),
                    ]),
                ]),
            ],
        ]));

        foreach ($violations as $violation) {
            $this->context->buildViolation(
                $violation->getMessage(),
                $violation->getParameters(),
            )
                ->atPath($violation->getPropertyPath())
                ->addViolation();
        }

        if (0 < $violations->count()) {
            return;
        }

        $this->validateSourceValuesAreUnique($operation['mapping']);
    }

    public function validateSourceValuesAreUnique(array $mapping): void
    {
        $uniqueValues = [];

        foreach ($mapping as $code => $sourceValues) {
            if (!empty(array_intersect($sourceValues, $uniqueValues))) {
                $this->context->buildViolation('akeneo.tailored_import.validation.operation.replacement.source_values_should_be_unique')
                    ->atPath(sprintf('[mapping][%s]', $code))
                    ->addViolation();
            }

            $uniqueValues = [...$uniqueValues, ...$sourceValues];
        }
    }
}
