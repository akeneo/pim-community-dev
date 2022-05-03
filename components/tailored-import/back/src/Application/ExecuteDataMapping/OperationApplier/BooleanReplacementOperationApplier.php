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

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\BooleanReplacementOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\BooleanValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NullValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class BooleanReplacementOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        if (!$operation instanceof BooleanReplacementOperation) {
            throw new \InvalidArgumentException(sprintf('Expecting %s, "%s" given', BooleanReplacementOperation::class, $operation::class));
        }

        if (!$value instanceof StringValue) {
            throw new \InvalidArgumentException(sprintf('Boolean replacement Operation only supports String value, "%s" given', $value::class));
        }

        if (!array_key_exists($value->getValue(), $operation->getMapping())) {
            return new NullValue();
        }

        return new BooleanValue($operation->getMapping()[$value->getValue()]);
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof BooleanReplacementOperation;
    }
}
