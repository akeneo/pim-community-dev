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

namespace Akeneo\AssetManager\Infrastructure\Validation\Attribute;

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
class MaxLengthValidator extends ConstraintValidator
{
    public function validate($maxLength, Constraint $constraint)
    {
        if (!$constraint instanceof MaxLength) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($maxLength, [
                new Constraints\Callback(function ($value, ExecutionContextInterface $context, $payload) {
                    if (null !== $value && !is_int($value)) {
                        $context->buildViolation(MaxLength::MESSAGE_SHOULD_BE_AN_INTEGER)
                            ->addViolation();
                    }
                }),
                new Constraints\LessThanOrEqual(65535),
                new Constraints\GreaterThan(0)
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
