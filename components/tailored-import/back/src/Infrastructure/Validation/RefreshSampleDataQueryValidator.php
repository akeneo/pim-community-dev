<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshSampleDataQueryValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof RefreshSampleDataQuery) {
            throw new UnexpectedTypeException($constraint, RefreshSampleDataQuery::class);
        }

        if (!$value instanceof Request) {
            return;
        }

        $requiredParams = [
            'file_key',
            'column_index',
            'sheet_name',
            'product_line',
            'current_sample',
        ];

        $missingParams = array_filter($requiredParams, fn ($param) => null === $value->get($param));

        if (count($missingParams) > 0) {
            $this->context->buildViolation(
                RefreshSampleDataQuery::MISSING_QUERY_PARAMS,
                [
                    '{{ missing_params }}' => implode(', ', $missingParams),
                ]
            )->addViolation();
        }
    }
}
