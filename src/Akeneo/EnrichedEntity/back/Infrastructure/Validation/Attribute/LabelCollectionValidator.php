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

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class LabelCollectionValidator extends ConstraintValidator
{
    /**
     * @param mixed      $localeCodes The value that should be validated
     * @param Constraint $constraint  The constraint for the validation
     */
    public function validate($localeCodes, Constraint $constraint)
    {
        if (!$constraint instanceof LabelCollection) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        $validator = Validation::createValidator();

        foreach ($localeCodes as $localeCode => $label) {
            $this->validateLocaleCode($validator, $localeCode);
            $this->validateLabelForLocale($validator, $localeCode, $label);
        }
    }

    /**
     * @param mixed $localeCode
     */
    private function validateLocaleCode(ValidatorInterface $validator, $localeCode): void
    {
        $violations = $validator->validate($localeCode, [
            new Constraints\Type(['type' => 'string']),
        ]);

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->addViolation(
                    sprintf('invalid locale code: %s', $violation->getMessage()),
                    $violation->getParameters()
                );
            }
        }
    }

    /**
     * @param mixed $label
     */
    private function validateLabelForLocale(ValidatorInterface $validator, $localeCode, $label): void
    {
        $violations = $validator->validate($label, [
            new Constraints\Type(['type' => 'string']),
        ]);

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->addViolation(
                    sprintf('invalid label for locale code "%s": %s', $localeCode, $violation->getMessage()),
                    $violation->getParameters()
                );
            }
        }
    }
}
