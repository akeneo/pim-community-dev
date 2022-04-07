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

class Operations extends Constraint
{
    public const MAX_COUNT_REACHED = 'akeneo.tailored_import.validation.operations.max_count_reached';

    public function __construct(
        private string $targetType,
    ) {
        parent::__construct();
    }

    public function getTargetType(): string
    {
        return $this->targetType;
    }

    public function validatedBy(): string
    {
        return 'akeneo.tailored_import.validation.operations';
    }
}
