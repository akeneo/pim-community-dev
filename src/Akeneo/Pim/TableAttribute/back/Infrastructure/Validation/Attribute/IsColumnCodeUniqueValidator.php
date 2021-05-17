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
        if (!is_array($value)) {
            return;
        }

        $codes = array_column($value, 'code');
        $duplicateCodes = array_unique(array_diff_key($codes, array_unique($codes)));
        if (count($duplicateCodes) > 0) {
            $this->context->buildViolation(
                "TODO IsColumnCodeUnique message %duplicateCodes%",
                [ '%duplicateCodes%' => join(', ', $duplicateCodes) ]
            )->addViolation();
        }
    }
}
