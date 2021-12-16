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

namespace Akeneo\SharedCatalog\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/** Use CssColorValidator from Symfony when we use symfony/validator 5.4 */
class CssColorValidator extends ConstraintValidator
{
    private const PATTERN_HEX = '/^#([a-f0-9]{6}|[a-f0-9]{3})$/i';

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CssColor) {
            throw new UnexpectedTypeException($constraint, CssColor::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (preg_match(self::PATTERN_HEX, (string) $value)) {
            return;
        }

        $this->context->buildViolation(CssColor::INVALID_COLOR_MESSAGE)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
