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

final class OptionsShouldOnlyBeSetOnSelectColumnsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, OptionsShouldOnlyBeSetOnSelectColumns::class);
        if (!\is_array($value) ||
            !isset($value['options']) ||
            !isset($value['data_type']) ||
            !\is_string($value['data_type']) ||
            SelectColumn::DATATYPE === $value['data_type']
        ) {
            return;
        }

        $this->context->buildViolation(
            'pim_table_configuration.validation.table_configuration.options_cannot_be_set',
            [
                '{{ data_type }}' => $value['data_type'],
            ]
        )->addViolation();
    }
}
