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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\OperationApplier;

use Akeneo\Platform\TailoredExport\Application\Common\Operation\OperationInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\ReplacementOperation;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\StringValue;

class BooleanReplacementOperationApplier implements OperationApplierInterface
{
    public function applyOperation(
        OperationInterface $operation,
        SourceValueInterface $value
    ): SourceValueInterface {
        if (
            !$operation instanceof ReplacementOperation
            || !$value instanceof BooleanValue
        ) {
            throw new \LogicException('Cannot apply Boolean replacement operation');
        }

        $data = $value->getData() ? 'true' : 'false';

        if ($operation->hasMappedValue($data)) {
            $mappedValue = $operation->getMappedValue($data);

            return new StringValue($mappedValue);
        }

        return $value;
    }

    public function supports(OperationInterface $operation, SourceValueInterface $value): bool
    {
        return $value instanceof BooleanValue && $operation instanceof ReplacementOperation;
    }
}
