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
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class MaxFileSizeValidator extends ConstraintValidator
{
    public function validate($maxFileSize, Constraint $constraint)
    {
        if (!$constraint instanceof MaxFileSize) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($maxFileSize, [
            new Constraints\Callback(function ($value, ExecutionContextInterface $context, $payload) {
                if (!(null === $value || is_float($value))) {
                    $context->buildViolation('This value should be a number')
                        ->addViolation();
                }
            })
        ]);

        if (null !== $maxFileSize && 0 === $violations->count()) {
            $violations->addAll($validator->validate((float) $maxFileSize, [
                new Constraints\LessThanOrEqual(9999.99),
                new Constraints\GreaterThan(0),
            ]));
        }

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
