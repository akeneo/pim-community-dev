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

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\EnrichedEntity;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validation;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class LabelCollectionValidator extends ConstraintValidator
{
    /**
     * @param mixed      $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        $validator = Validation::createValidator();

        foreach ($value as $localeCode => $label) {
            $violations = $validator->validate($localeCode, [
                new Constraints\NotBlank(),
                new Constraints\NotNull(),
                new Constraints\Type([
                    'type' => 'string',
                ]),
            ]);

            if ($violations->count() > 0) {
                foreach ($violations as $violation) {
                    $this->context->addViolation(
                        sprintf('Invalid key: %s', $violation->getMessage()),
                        $violation->getParameters()
                    );
                }
            }
        }
    }
}
