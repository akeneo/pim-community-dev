<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DuplicateOptionsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, DuplicateOptions::class);
        if (!is_array($value) || \count($value) <= 1) {
            return;
        }
        Assert::allString($value);

        $dataLowercase = \array_map('strtolower', $value);
        $uniqueDataLowercase = \array_unique($dataLowercase, SORT_STRING);
        $duplicateOptionsLowerCase = \array_unique(\array_diff_key($dataLowercase, $uniqueDataLowercase));

        if (\count($duplicateOptionsLowerCase) > 0) {
            // get the duplicate options in the original case submitted by the user
            $duplicateOptions = [];
            foreach (\array_keys($duplicateOptionsLowerCase) as $index) {
                $duplicateOptions[] = $value[$index];
            }

            $this->context->buildViolation(
                $constraint->message,
                [
                    '{{ duplicate_options }}' => \implode(', ', $duplicateOptions),
                    '%count%' => count($duplicateOptions),
                ]
            )->addViolation();
        }
    }
}
