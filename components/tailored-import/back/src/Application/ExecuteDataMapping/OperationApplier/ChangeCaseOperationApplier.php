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
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ChangeCaseOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class ChangeCaseOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        return match (true) {
            !$operation instanceof ChangeCaseOperation => throw new UnexpectedValueException($operation, ChangeCaseOperation::class, self::class),
            $value instanceof InvalidValue => $value,
            $value instanceof StringValue => $this->findModeAndApply($operation, $value),
            default => throw new UnexpectedValueException($value, StringValue::class, self::class)
        };
    }

    private function findModeAndApply(ChangeCaseOperation $operation, StringValue $value): StringValue
    {
        return match ($operation->getMode()) {
            ChangeCaseOperation::MODE_UPPERCASE => new StringValue(\mb_strtoupper($value->getValue())),
            ChangeCaseOperation::MODE_LOWERCASE => new StringValue(\mb_strtolower($value->getValue())),
            ChangeCaseOperation::MODE_CAPITALIZE => new StringValue($this->safeUcFirst($value->getValue())),
            default => throw new \RuntimeException('Unsupported change case mode'),
        };
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof ChangeCaseOperation;
    }

    private function safeUcFirst(string $string): string
    {
        $firstLetter = \mb_substr($string, 0, 1);
        $rest = \mb_substr($string, 1);

        return \sprintf('%s%s', \mb_strtoupper($firstLetter), \mb_strtolower($rest));
    }
}
