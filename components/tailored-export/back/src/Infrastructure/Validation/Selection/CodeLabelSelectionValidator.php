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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Selection;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\LocaleShouldBeActive;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;

class CodeLabelSelectionValidator extends ConstraintValidator
{
    public function validate($selection, Constraint $constraint): void
    {
        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($selection, [
            new Collection(
                [
                    'fields' => [
                        'type' => new Choice(
                            [
                                'choices' => [
                                    'code',
                                    'label',
                                ],
                            ],
                        ),
                        'locale' => new Optional([new Type(['type' => 'string'])]),
                    ],
                ],
            ),
        ]);

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        if ('label' === $selection['type']) {
            $validator->inContext($this->context)->atPath('[locale]')->validate($selection['locale'], [
                new NotBlank(),
                new LocaleShouldBeActive(),
            ]);
        }
    }
}
