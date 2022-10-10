<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 */
class RecordShouldNotBeUsedAsProductVariantAxis extends Constraint
{
    public const ERROR_MESSAGE = 'pim_reference_entity.record.validation.record.should_not_be_used_as_product_variant_axis';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return 'akeneo_referenceentity.validator.record.record_should_not_be_used_as_product_variant_axis';
    }
}
