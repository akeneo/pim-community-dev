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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping;

use Symfony\Component\Validator\Constraint;

final class Operations extends Constraint
{
    public const INCOMPATIBLE_OPERATION_TYPE = 'akeneo.tailored_import.validation.operations.incompatible_operation_type';
    public const OPERATION_TYPE_DOES_NOT_EXIST = 'akeneo.tailored_import.validation.operations.operation_type_does_not_exist';

    public function __construct(
        private array $compatibleOperations,
    ) {
        parent::__construct();
    }

    public function getCompatibleOperations(): array
    {
        return $this->compatibleOperations;
    }
}
