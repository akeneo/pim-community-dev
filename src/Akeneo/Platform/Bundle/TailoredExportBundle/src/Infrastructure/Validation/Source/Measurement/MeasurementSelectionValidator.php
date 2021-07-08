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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Source\Measurement;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\Measurement\MeasurementAmountSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Measurement\MeasurementUnitCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Measurement\MeasurementUnitLabelSelection;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\LocaleShouldBeActive;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;

class MeasurementSelectionValidator extends ConstraintValidator
{
    public function validate($selection, Constraint $constraint)
    {
        $validator = $this->context->getValidator();
        $violations = $validator->validate($selection, new Collection(
            [
                'fields' => [
                    'type' => new Choice(
                        [
                            'choices' => [
                                MeasurementUnitCodeSelection::TYPE,
                                MeasurementUnitLabelSelection::TYPE,
                                MeasurementAmountSelection::TYPE,
                            ],
                        ]
                    ),
                    'locale' => new Optional([new Type(['type' => 'string'])]),
                ],
            ]
        ));

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

        if (MeasurementUnitLabelSelection::TYPE === $selection['type']) {
            $violations = $validator->validate($selection['locale'], [
                new NotBlank(),
                new LocaleShouldBeActive()
            ]);

            foreach ($violations as $violation) {
                $this->context->buildViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                )
                    ->atPath('[locale]')
                    ->addViolation();
            }
        }
    }
}
