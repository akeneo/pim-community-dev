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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Source\Enabled;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;

class EnabledReplacementOperationValidator extends ConstraintValidator
{
    public function validate($operation, Constraint $constraint)
    {
        $validator = $this->context->getValidator();

        $violations = $validator->validate($operation, new Collection([
            'fields' => [
                'type' => new EqualTo(['value' => 'replacement']),
                'mapping' => new Collection([
                    'fields' => [
                        'true' => [new NotBlank(), new Length(['max' => 255])],
                        'false' => [new NotBlank(), new Length(['max' => 255])],
                    ]
                ])
            ]
        ]));

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
