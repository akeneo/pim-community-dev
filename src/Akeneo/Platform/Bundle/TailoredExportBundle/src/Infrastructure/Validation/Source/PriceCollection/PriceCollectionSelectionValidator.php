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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Source\PriceCollection;

use Akeneo\Platform\TailoredExport\Domain\Model\Selection\PriceCollection\PriceCollectionAmountSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\PriceCollection\PriceCollectionCurrencyCodeSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\PriceCollection\PriceCollectionCurrencyLabelSelection;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\LocaleShouldBeActive;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;

class PriceCollectionSelectionValidator extends ConstraintValidator
{
    private array $availableCollectionSeparator;

    public function __construct(array $availableCollectionSeparator)
    {
        $this->availableCollectionSeparator = $availableCollectionSeparator;
    }

    public function validate($selection, Constraint $constraint)
    {
        $validator = $this->context->getValidator();
        $violations = $validator->validate($selection, new Collection(
            [
                'fields' => [
                    'type' => new Choice(
                        [
                            'choices' => [
                                PriceCollectionCurrencyCodeSelection::TYPE,
                                PriceCollectionCurrencyLabelSelection::TYPE,
                                PriceCollectionAmountSelection::TYPE,
                            ],
                        ]
                    ),
                    'locale' => new Optional([new Type(['type' => 'string'])]),
                    'separator' => new Choice(
                        [
                            'choices' => $this->availableCollectionSeparator,
                        ]
                    ),
                ],
            ]
        ));
        foreach ($violations as $violation) {
            $this->context->buildViolation(
                $violation->getMessage(),
                $violation->getParameters()
            )
                ->atPath($violation->getPropertyPath())
                ->addViolation();
        }

        if (PriceCollectionCurrencyLabelSelection::TYPE === $selection['type']) {
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
