<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier;

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\UnexpectedValueException;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\ConvertToDateOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\DateValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConvertToDateOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        if (!$operation instanceof ConvertToDateOperation) {
            throw new UnexpectedValueException($operation, ConvertToDateOperation::class, self::class);
        }

        if (!$value instanceof StringValue) {
            throw new UnexpectedValueException($value, StringValue::class, self::class);
        }

        $date = \DateTimeImmutable::createFromFormat(
            $operation::DATE_FORMAT_TO_PHP_DATE_FORMAT_MAPPING[$operation->getDateFormat()],
            $value->getValue(),
            new \DateTimeZone('UTC'),
        )->setTime(0, 0);

        return new DateValue($date);
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof ConvertToDateOperation;
    }
}
