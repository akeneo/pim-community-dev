<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\Common;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validation;

class CodeValidator extends ConstraintValidator
{
    private const MAX_CODE_LENGTH = 255;

    public function validate($code, Constraint $constraint)
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($code, [
                new Constraints\NotBlank(),
                new Constraints\Type(['type' => 'string']),
                new Constraints\Length(['max' => self::MAX_CODE_LENGTH]),
                new Constraints\Regex([
                        'pattern' => '/^[a-zA-Z0-9_]+$/',
                        'message' => 'pim_measurements.validation.common.code.pattern',
                    ]
                ),
            ]
        );

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->addViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                );
            }
        }
    }
}
