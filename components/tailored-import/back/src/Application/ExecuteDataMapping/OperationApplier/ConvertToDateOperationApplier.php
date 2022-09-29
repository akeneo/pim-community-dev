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
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToDateOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\DateValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class ConvertToDateOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        return match (true) {
            !$operation instanceof ConvertToDateOperation => throw new UnexpectedValueException($operation, ConvertToDateOperation::class, self::class),
            $value instanceof InvalidValue => $value,
            $value instanceof StringValue => $this->convertToDateValue($operation, $value),
            default => throw new UnexpectedValueException($value, StringValue::class, self::class),
        };
    }

    private function convertToDateValue(ConvertToDateOperation $operation, StringValue $value): DateValue|InvalidValue
    {
        $date = \DateTimeImmutable::createFromFormat(
            $operation::DATE_FORMAT_TO_PHP_DATE_FORMAT_MAPPING[$operation->getDateFormat()],
            $value->getValue(),
            new \DateTimeZone('UTC'),
        );

        if (false === $date) {
            return new InvalidValue(sprintf(
                'Cannot format date "%s" with provided format "%s"',
                $value->getValue(),
                $operation->getDateFormat(),
            ));
        }

        return new DateValue($date->setTime(0, 0));
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof ConvertToDateOperation;
    }
}
