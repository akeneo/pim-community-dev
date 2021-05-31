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
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
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
            new Callback(function ($value, ExecutionContextInterface $context, $payload) {
                if (null !== $value && !is_numeric($value)) {
                    $context->buildViolation(MaxFileSize::MESSAGE_SHOULD_BE_A_NUMBER)->addViolation();
                }
            }),
        ]);

        if (null !== $maxFileSize && 0 === $violations->count()) {
            $violations->addAll($validator->validate((float) $maxFileSize, [
                new LessThanOrEqual(9999.99),
                new GreaterThan(0),
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
