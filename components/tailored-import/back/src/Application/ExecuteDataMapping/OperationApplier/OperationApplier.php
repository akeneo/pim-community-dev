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

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NullValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class OperationApplier
{
    /**
     * @param OperationApplierInterface[] $operationAppliers
     */
    public function __construct(
        private iterable $operationAppliers,
    ) {
    }

    public function applyOperations(
        OperationCollection $operationCollection,
        ValueInterface $value,
    ): ValueInterface {
        if ($value instanceof NullValue) {
            return $value;
        }

        foreach ($operationCollection as $operation) {
            $applier = $this->getApplier($operation);

            if (!$applier instanceof OperationApplierInterface) {
                continue;
            }

            $value = $applier->applyOperation($operation, $value);
        }

        return $value;
    }

    public function applyOperationWithIndexedResults(
        OperationCollection $operationCollection,
        ValueInterface $values,
    ): array {
        $operationValues = [];

        if (empty($operationCollection->normalize())) {
            return $operationValues;
        }

        foreach ($operationCollection as $operation) {
            $applier = $this->getApplier($operation);

            if (!$applier instanceof OperationApplierInterface) {
                continue;
            }

            foreach ($values->getValue() as $value) {
                $operationValues[$operation->normalize()['uuid']][] = null === $value ? null : $applier->applyOperation($operation, new StringValue($value))->getValue();
            }
        }

        return $operationValues;
    }

    private function getApplier(OperationInterface $operation): OperationApplierInterface|null
    {
        foreach ($this->operationAppliers as $applier) {
            if ($applier->supports($operation)) {
                return $applier;
            }
        }

        return null;
    }
}
