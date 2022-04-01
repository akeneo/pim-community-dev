<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Akeneo\Platform\TailoredImport\Application\SampleData\GetSampleData\GetSampleDataQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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

        $missingParams = "";

        if (null === $value->get('file_key')) {
            $missingParams += ' file_key,';
        }

        if (null === $value->get('column_index')) {
            $missingParams += ' column_index,';
        }

        if (null === $value->get('sheet_name')) {
            $missingParams += ' sheet_name,';
        }

        if (null === $value->get('product_line')) {
            $missingParams += ' product_line';
        }

        if ("" !== $missingParams) {
            $this->context->buildViolation(
                SampleDataQuery::MISSING_PROPERTY,
                [
                    '{{ missing_params }}' => $missingParams,
                ]
            )->addViolation();
        }
    }
}