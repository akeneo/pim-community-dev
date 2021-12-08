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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class ColumnDatatypeValidator extends ConstraintValidator
{
    private array $allowedColumnDatatypes;

    public function __construct(array $allowedColumnDatatypes)
    {
        $this->allowedColumnDatatypes = $allowedColumnDatatypes;
    }

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ColumnDatatype::class);
        if (!is_string($value)) {
            return;
        }

        if (!in_array($value, $this->allowedColumnDatatypes)) {
            $this->context->buildViolation(
                'pim_table_configuration.validation.table_configuration.unknown_data_type',
                [
                    '{{ allowed_data_types }}' => implode(', ', $this->allowedColumnDatatypes),
                ]
            )->atPath('column')->addViolation();
        }
    }
}
