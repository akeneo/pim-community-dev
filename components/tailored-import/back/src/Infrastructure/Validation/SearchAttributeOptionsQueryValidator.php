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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SearchAttributeOptionsQueryValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SearchAttributeOptionsQuery) {
            throw new UnexpectedTypeException($constraint, SearchAttributeOptionsQuery::class);
        }

        if (!$value instanceof Request) {
            return;
        }

        $requiredParams = [
            'attribute_code',
            'search',
            'locale',
            'page',
        ];

        $missingParams = array_filter($requiredParams, static fn (string $param) => null === $value->get($param));

        if (!empty($missingParams)) {
            $this->context->buildViolation(
                SearchAttributeOptionsQuery::MISSING_QUERY_PARAMS,
                ['{{ missing_params }}' => implode(', ', $missingParams)],
            )->addViolation();
        }
    }
}
