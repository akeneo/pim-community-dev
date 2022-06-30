<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DuplicateRecordsValidator extends ConstraintValidator
{
    public function validate($values, Constraint $constraint)
    {
        if (!$constraint instanceof DuplicateRecords) {
            throw new UnexpectedTypeException($constraint, DuplicateRecords::class);
        }

        if (!is_array($values) || count($values) <= 1) {
            return;
        }

        $lowercaseCodes = array_map('strtolower', $values);
        $uniqueLowercaseCodes = array_unique($lowercaseCodes);
        $duplicateLowercaseCodes = array_unique(array_diff_key($lowercaseCodes, $uniqueLowercaseCodes));

        if (count($duplicateLowercaseCodes) > 0) {
            $duplicateCodes = [];
            foreach (array_keys($duplicateLowercaseCodes) as $index) {
                $duplicateCodes[] = $values[$index];
            }

            $this->context->buildViolation(
                $constraint->message,
                [
                    '{{ duplicate_codes }}' => implode(', ', $duplicateCodes),
                    '%count%' => count($duplicateCodes),
                    '%attribute_code%' => $constraint->attributeCode,
                ]
            )->setCode(DuplicateRecords::PROPERTY_CONSTRAINT)->addViolation();
        }
    }
}
