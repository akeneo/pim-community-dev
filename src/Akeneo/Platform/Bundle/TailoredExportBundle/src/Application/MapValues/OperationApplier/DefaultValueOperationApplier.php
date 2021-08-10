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

use Akeneo\Platform\TailoredExport\Domain\Model\Operation\DefaultValueOperation;
use Akeneo\Platform\TailoredExport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\StringValue;

class DefaultValueOperationApplier implements OperationApplierInterface
{
    public function applyOperation(
        OperationInterface $operation,
        SourceValueInterface $value
    ): SourceValueInterface {
        if (
            !$operation instanceof DefaultValueOperation
            || !$value instanceof NullValue
        ) {
            throw new \LogicException('Cannot apply Default value operation');
        }

        return new StringValue($operation->getDefaultValue());
    }

    public function supports(OperationInterface $operation, SourceValueInterface $value): bool
    {
        return $value instanceof NullValue && $operation instanceof DefaultValueOperation;
    }
}
