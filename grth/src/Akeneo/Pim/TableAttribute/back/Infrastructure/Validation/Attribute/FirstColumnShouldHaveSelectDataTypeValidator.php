<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class FirstColumnShouldHaveSelectDataTypeValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, FirstColumnShouldHaveSelectDataType::class);
        if (!is_array($value) || [] === $value) {
            return;
        }

        $firstColumnDefinition = current($value);
        $firstColumnDataType = $firstColumnDefinition['data_type'] ?? null;

        if (is_string($firstColumnDataType) && SelectColumn::DATATYPE !== $firstColumnDataType) {
            $this->context
                ->buildViolation($constraint->message, ['{{ data_type }}' => $firstColumnDataType])
                ->atPath('[0].data_type')
                ->addViolation();
        }
    }
}
