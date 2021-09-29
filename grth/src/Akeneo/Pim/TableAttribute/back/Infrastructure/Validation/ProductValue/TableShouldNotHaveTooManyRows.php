<?php

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Validator\Constraint;

class TableShouldNotHaveTooManyRows extends Constraint
{
    public const LIMIT = 100;
    public string $message = 'pim_table_configuration.validation.product_value.too_many_rows';

    public function getTargets(): array
    {
        return [Constraint::CLASS_CONSTRAINT];
    }
}
