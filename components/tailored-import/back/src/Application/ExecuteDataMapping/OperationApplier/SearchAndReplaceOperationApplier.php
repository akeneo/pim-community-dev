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

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\UnexpectedValueException;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SearchAndReplaceOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NullValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class SearchAndReplaceOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        return match (true) {
            !$operation instanceof SearchAndReplaceOperation => throw new UnexpectedValueException($operation, SearchAndReplaceOperation::class, self::class),
            $value instanceof InvalidValue => $value,
            $value instanceof StringValue => $this->searchAndReplace($operation, $value),
            default => throw new UnexpectedValueException($value, StringValue::class, self::class),
        };
    }

    private function searchAndReplace(SearchAndReplaceOperation $operation, StringValue $value): StringValue|NullValue
    {
        $stringValue = $value->getValue();

        foreach ($operation->getReplacements() as $replacement) {
            if ($replacement->isCaseSensitive()) {
                $stringValue = str_replace($replacement->getWhat(), $replacement->getWith(), $stringValue);
            } else {
                $stringValue = str_ireplace($replacement->getWhat(), $replacement->getWith(), $stringValue);
            }
        }

        if ('' === $stringValue) {
            return new NullValue();
        }

        return new StringValue($stringValue);
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof SearchAndReplaceOperation;
    }
}
