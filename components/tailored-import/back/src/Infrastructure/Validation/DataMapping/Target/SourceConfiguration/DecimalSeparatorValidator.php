<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\SourceConfiguration;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class DecimalSeparatorValidator extends ConstraintValidator
{
    public function __construct(
        private array $availableDecimalSeparators,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof DecimalSeparator) {
            throw new UnexpectedTypeException($constraint, DecimalSeparator::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($value, new Choice($this->availableDecimalSeparators));
    }
}
