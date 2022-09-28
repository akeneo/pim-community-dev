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
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToPriceOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\PriceValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class ConvertToPriceOperationApplier implements OperationApplierInterface
{
    private const DEFAULT_DECIMAL_SEPARATOR = '.';

    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        return match (true) {
            !$operation instanceof ConvertToPriceOperation => throw new UnexpectedValueException($operation, ConvertToPriceOperation::class, self::class),
            $value instanceof InvalidValue => $value,
            $value instanceof StringValue => $this->convertToPrice($operation, $value),
            default => throw new UnexpectedValueException($value, StringValue::class, self::class)
        };
    }

    private function convertToPrice(ConvertToPriceOperation $operation, StringValue $value): InvalidValue|PriceValue
    {
        $numberValue = str_replace(
            $operation->getDecimalSeparator(),
            self::DEFAULT_DECIMAL_SEPARATOR,
            $value->getValue(),
        );

        if (!is_numeric($numberValue)) {
            return new InvalidValue(sprintf(
                'Cannot convert "%s" to a number with separator "%s"',
                $value->getValue(),
                $operation->getDecimalSeparator(),
            ));
        }

        return new PriceValue($numberValue, $operation->getCurrency());
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof ConvertToPriceOperation;
    }
}
