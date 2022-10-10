<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ShouldStayOwnerOfTheProduct extends Constraint
{
    public string $message = 'pim_enrich.product.validation.upsert.should_keep_one_owned_category';

    /**
     * {@inheritDoc}
     */
    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
