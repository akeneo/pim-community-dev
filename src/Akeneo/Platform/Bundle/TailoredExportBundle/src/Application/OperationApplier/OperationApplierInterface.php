<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Application\OperationApplier;

use Akeneo\Platform\TailoredExport\Domain\Operation;
use Akeneo\Platform\TailoredExport\Domain\SourceValue;

interface OperationApplierInterface
{
    public function applyOperation(Operation $operation, SourceValue $value): SourceValue;

    public function supports(Operation $operation, SourceValue $value);
}
