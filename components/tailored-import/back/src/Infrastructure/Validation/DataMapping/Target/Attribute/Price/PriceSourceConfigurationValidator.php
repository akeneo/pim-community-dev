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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Attribute\Price;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Attribute\SourceConfiguration\DecimalSeparator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class PriceSourceConfigurationValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PriceSourceConfiguration) {
            throw new UnexpectedTypeException($constraint, PriceSourceConfiguration::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($value, new Collection([
            'currency' => new Currency($constraint->getChannelCode()),
            'decimal_separator' => new DecimalSeparator(),
        ]));
    }
}
