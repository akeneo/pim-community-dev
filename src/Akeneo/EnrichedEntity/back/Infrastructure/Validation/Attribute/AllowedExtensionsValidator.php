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
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class AllowedExtensionsValidator extends ConstraintValidator
{
    public function validate($allowedExtensions, Constraint $constraint)
    {
        if (!$constraint instanceof AllowedExtensions) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($allowedExtensions, [new Assert\Type('array')]);

        if ($violations->count() > 0) {
            $this->addViolations($violations);

            return;
        }

        foreach ($allowedExtensions as $allowedExtension) {
            $violations = $validator->validate($allowedExtension, [new Assert\Type('string')]);
            if ($violations->count() > 0 ) {
                $this->addViolations($violations);
            }
        }

    }

    private function addViolations(ConstraintViolationListInterface $violations): void
    {
        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->addViolation($violation->getMessage(), $violation->getParameters());
            }
        }
    }
}
