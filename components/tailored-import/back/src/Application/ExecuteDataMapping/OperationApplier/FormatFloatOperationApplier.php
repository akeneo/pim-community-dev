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
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\FormatFloatOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;

final class FormatFloatOperationApplier implements OperationApplierInterface
{
    private const DEFAULT_DECIMAL_SEPARATOR = '.';

    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        if (!$operation instanceof FormatFloatOperation) {
            throw new UnexpectedValueException($operation, FormatFloatOperation::class, self::class);
        }

        if ($value instanceof InvalidValue) {
            return $value;
        }

        if (!$value instanceof StringValue) {
            throw new UnexpectedValueException($value, StringValue::class, self::class);
        }

        $floatValue = str_replace(
            $operation->getDecimalSeparator(),
            self::DEFAULT_DECIMAL_SEPARATOR,
            $value->getValue(),
        );

        if (!is_numeric($floatValue)) {
            return new InvalidValue(sprintf(
                'Cannot convert "%s" to a number with separator "%s"',
                $value->getValue(),
                $operation->getDecimalSeparator(),
            ));
        }

        return new StringValue(number_format(
            (float) $floatValue,
            decimals: MeasureConverter::SCALE,
            thousands_separator: '',
        ));
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof FormatFloatOperation;
    }
}
