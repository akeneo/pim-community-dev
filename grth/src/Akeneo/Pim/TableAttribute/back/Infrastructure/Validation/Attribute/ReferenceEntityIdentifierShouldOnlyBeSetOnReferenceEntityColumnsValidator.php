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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class ReferenceEntityIdentifierShouldOnlyBeSetOnReferenceEntityColumnsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, ReferenceEntityIdentifierShouldOnlyBeSetOnReferenceEntityColumns::class);
        if (!\is_array($value) ||
            !isset($value['data_type']) ||
            !\is_string($value['data_type'])
        ) {
            return;
        }

        if (
            !isset($value['reference_entity_identifier']) &&
            ReferenceEntityColumn::DATATYPE === $value['data_type']
        ) {
            $this->context->buildViolation(
                'pim_table_configuration.validation.table_configuration.reference_entity_identifier_must_be_filled'
            )->addViolation();
        } elseif (
            isset($value['reference_entity_identifier']) &&
            ReferenceEntityColumn::DATATYPE !== $value['data_type']
        ) {
            $this->context->buildViolation(
                'pim_table_configuration.validation.table_configuration.reference_entity_identifier_cannot_be_set',
                [
                    '{{ data_type }}' => $value['data_type'],
                ]
            )->addViolation();
        }
    }
}
