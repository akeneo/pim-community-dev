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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Operation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;

class ReplacementOperationValidator extends ConstraintValidator
{
    public function validate($operation, Constraint $constraint): void
    {
        $validator = $this->context->getValidator();

        $validator->inContext($this->context)->validate($operation, new Collection([
            'fields' => [
                'type' => new EqualTo(['value' => 'replacement']),
                'mapping' => new All([
                    new NotBlank([
                        'message' => 'akeneo.tailored_export.validation.required',
                    ]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'akeneo.tailored_export.validation.max_length_reached',
                    ]),
                ]),
            ],
        ]));
    }
}
