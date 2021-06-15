<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Selection;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotBlank;
use Akeneo\Platform\TailoredExport\Domain\SelectionTypes;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\LocaleShouldBeActive;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validation;

class CodeLabelCollectionSelectionValidator extends ConstraintValidator
{
    /** @var string[] */
    private array $availableCollectionSeparator;

    public function __construct(array $availableCollectionSeparator)
    {
        $this->availableCollectionSeparator = $availableCollectionSeparator;
    }

    public function validate($selection, Constraint $constraint)
    {
        $validator = $this->context->getValidator();
        $violations = $validator->validate($selection, [
            new Collection(
                [
                    'fields' => [
                        'type' => [
                            new Choice(
                                [
                                    'strict' => true,
                                    'choices' => [
                                        SelectionTypes::CODE,
                                        SelectionTypes::LABEL,
                                    ],
                                ]
                            )
                        ],
                        'separator' => [
                            new Choice(
                                [
                                    'strict' => true,
                                    'choices' => $this->availableCollectionSeparator,
                                ]
                            )
                        ],
                        'label' => new Type(['type' => 'string']),
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
                    ->addViolation();
            }

            return;
        }

        if (SelectionTypes::LABEL === $selection['type']) {
            $violations = $validator->validate($selection['locale'] ?? null, [
                new NotBlank(),
                new LocaleShouldBeActive()
            ]);

            foreach ($violations as $violation) {
                $this->context->buildViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                )
                    ->atPath('locale')
                    ->addViolation();
            }
        }
    }
}
