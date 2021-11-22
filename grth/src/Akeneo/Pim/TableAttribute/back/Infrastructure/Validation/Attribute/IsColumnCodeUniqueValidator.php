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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class IsColumnCodeUniqueValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, IsColumnCodeUnique::class);
        if (!\is_array($value)) {
            return;
        }

        $codes = \array_column($value, 'code');
        $lowercaseCodes = \array_map('strtolower', $codes);
        $lowercaseDuplicateCodes = \array_diff_key($lowercaseCodes, \array_unique($lowercaseCodes));
        $duplicateCodes = \array_intersect_key($codes, $lowercaseDuplicateCodes);
        if ($duplicateCodes !== []) {
            $this->context->buildViolation(
                'pim_table_configuration.validation.table_configuration.duplicated_column_code',
                [
                    '%duplicateCodes%' => \implode(', ', $duplicateCodes),
                    '%count%' => \count($duplicateCodes),
                ],
            )->addViolation();
        }
    }
}
