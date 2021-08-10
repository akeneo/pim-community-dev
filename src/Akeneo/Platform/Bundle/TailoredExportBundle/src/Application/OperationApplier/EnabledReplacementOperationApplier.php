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

use Akeneo\Platform\TailoredExport\Application\Query\Operation\OperationInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Operation\ReplacementOperation;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\EnabledValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\StringValue;

class EnabledReplacementOperationApplier implements OperationApplierInterface
{
    public function applyOperation(
        OperationInterface $operation,
        SourceValueInterface $value
    ): SourceValueInterface {
        if (
            !$operation instanceof ReplacementOperation
            || !$value instanceof EnabledValue
        ) {
            throw new \LogicException('Cannot apply Enabled replacement operation');
        }

        $data = $value->isEnabled() ? 'true' : 'false';

        if ($operation->hasMappedValue($data)) {
            $mappedValue = $operation->getMappedValue($data);

            return new StringValue($mappedValue);
        }

        return $value;
    }

    public function supports(OperationInterface $operation, SourceValueInterface $value): bool
    {
        return $value instanceof EnabledValue && $operation instanceof ReplacementOperation;
    }
}
