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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

use Symfony\Component\Validator\Constraint;

final class TableValidationsShouldMatch extends Constraint
{
    public const MIN_MESSAGE = 'pim_table_configuration.validation.product_value.min_validation';
    public const MAX_MESSAGE = 'pim_table_configuration.validation.product_value.max_validation';
    public const DECIMALS_ALLOWED_MESSAGE = 'pim_table_configuration.validation.product_value.value_integer_required';
    public const MAX_LENGTH_MESSAGE = 'pim_table_configuration.validation.product_value.max_length_validation';

    /**
     * {@inheritDoc}
     */
    public function getTargets(): array
    {
        return [Constraint::CLASS_CONSTRAINT];
    }
}
