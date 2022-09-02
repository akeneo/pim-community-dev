<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation;

use Symfony\Component\Validator\Constraint;

abstract class OperationConstraint extends Constraint
{
    public const SOURCE_VALUES_SHOULD_BE_UNIQUE = 'akeneo.tailored_import.validation.source_values_should_be_unique';
    public const REQUIRED = 'akeneo.tailored_import.validation.required';
    public const MAX_LENGTH_REACHED = 'akeneo.tailored_import.validation.max_length_reached';
}
