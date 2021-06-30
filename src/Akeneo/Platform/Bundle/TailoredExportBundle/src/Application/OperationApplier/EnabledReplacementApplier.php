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
use Akeneo\Platform\TailoredExport\Domain\ReplacementOperation;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\EnabledValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\StringValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue;

class EnabledReplacementApplier implements OperationApplierInterface
{
    public function applyOperation(Operation $operation, SourceValue $value): SourceValue
    {
        if (!$operation instanceof ReplacementOperation) {
            throw new \Exception('NOOOOOOO');
        }

        if (!$value instanceof EnabledValue) {
            throw new \Exception('NOOOOOOO');
        }

        $data = $value->getData() ? 'true' : 'false';
        if ($operation->hasMappedValue($data)) {
            $mappedValue = $operation->getMappedValue($data);

            return new StringValue($mappedValue);
        }

        return $value;
    }

    public function supports(Operation $operation, SourceValue $value): bool
    {
        return $value instanceof EnabledValue && $operation instanceof ReplacementOperation;
    }
}
