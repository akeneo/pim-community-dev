<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Selection;

use Akeneo\Platform\TailoredExport\Domain\SelectionTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;

class FileSelectionValidator extends ConstraintValidator
{
    public function validate($selection, Constraint $constraint)
    {
        $validator = $this->context->getValidator();
        $violations = $validator->validate($selection, [
            new Collection(
                [
                    'fields' => [
                        'type' => [
                            new NotBlank(),
                            new Choice(
                                [
                                    'strict' => true,
                                    'choices' => [
                                        SelectionTypes::PATH,
                                        SelectionTypes::KEY,
                                        SelectionTypes::NAME,
                                    ],
                                ]
                            )
                        ],
                    ],
                ]
            ),
        ]);

        if (0 < $violations->count()) {
            foreach ($violations as $violation) {
                $this->context->buildViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                )
                    ->atPath($violation->getPropertyPath())
                    ->addViolation();
            }
        }
    }
}
