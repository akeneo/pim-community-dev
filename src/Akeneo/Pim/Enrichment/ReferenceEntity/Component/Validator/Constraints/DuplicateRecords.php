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

final class DuplicateRecords extends Constraint
{
    public string $message = 'pim_reference_entity.product_value.validation.records_should_not_contains_duplicates';
    public string $attributeCode;

    public function getRequiredOptions(): array
    {
        return ['attributeCode'];
    }

    public function getDefaultOption(): string
    {
        return 'attributeCode';
    }
}
