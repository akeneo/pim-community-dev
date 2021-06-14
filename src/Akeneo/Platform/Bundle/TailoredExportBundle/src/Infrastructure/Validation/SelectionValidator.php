<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation;

use Akeneo\Platform\TailoredExport\Domain\SelectionTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validation;

class SelectionValidator extends ConstraintValidator
{
    public function validate($selection, Constraint $constraint)
    {
        $validator = Validation::createValidator();

        $violations = $validator->validate($selection, [
            new Collection(
                [
                    'fields' => [
                        'type' => [
                            new Choice(
                                [
                                    'strict' => true,
                                    'choices' => [
                                        SelectionTypes::AMOUNT,
                                        SelectionTypes::CODE,
                                        SelectionTypes::CURRENCY,
                                        SelectionTypes::LABEL,
                                    ],
                                ]
                            )
                        ],
                    ],
                    'allowExtraFields' => true,
                ]
            ),
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

        switch ($selection['type']) {
            case SelectionTypes::LABEL:
                $this->validateLabel($selection);
                break;
        }
    }

    private function validateLabel($selection)
    {
        if (!isset($selection['locale'])) {
            $this->context->buildViolation(Selection::SELECTION_LOCALE_SHOULD_NOT_BE_BLANK)
                ->addViolation();
        }
    }
}
