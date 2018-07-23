<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Record;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validation;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CodeValidator extends ConstraintValidator
{
    private const MAX_IDENTIFIER_LENGTH = 255;

    public function validate($identifier, Constraint $constraint)
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($identifier, [
                new Constraints\NotBlank(),
                new Constraints\NotNull(),
                new Constraints\Type(['type' => 'string']),
                new Constraints\Length(['max' => self::MAX_IDENTIFIER_LENGTH, 'min' => 1]),
                new Constraints\Regex([
                        'pattern' => '/^[a-zA-Z0-9_]+$/',
                        'message' => 'pim_enriched_entity.record.validation.identifier.pattern',
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
