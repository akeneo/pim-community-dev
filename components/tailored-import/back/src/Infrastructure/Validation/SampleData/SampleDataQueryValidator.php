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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\SampleData;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SampleDataQueryValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SampleDataQuery) {
            throw new UnexpectedTypeException($constraint, SampleDataQuery::class);
        }

        if (!$value instanceof Request) {
            return;
        }

        $requiredParams = [
            'file_key',
            'column_indices',
            'sheet_name',
            'product_line',
        ];

        $missingParams = array_filter($requiredParams, static fn ($param) => null === $value->get($param));

        if (count($missingParams) > 0) {
            $this->context->buildViolation(
                SampleDataQuery::MISSING_QUERY_PARAMS,
                [
                    '{{ missing_params }}' => implode(', ', $missingParams),
                ],
            )->addViolation();
        }
    }
}
