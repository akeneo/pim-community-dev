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

namespace Akeneo\Platform\Syndication\Application\MapValues\OperationApplier;

use Akeneo\Platform\Syndication\Application\Common\Operation\MeasurementConversionOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\OperationInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Domain\Query\MeasurementConverterInterface;

class MeasurementConversionOperationApplier implements OperationApplierInterface
{
    private MeasurementConverterInterface $measurementConverter;

    public function __construct(
        MeasurementConverterInterface $measurementConverter
    ) {
        $this->measurementConverter = $measurementConverter;
    }

    public function applyOperation(
        OperationInterface $operation,
        SourceValueInterface $value
    ): SourceValueInterface {
        if (
            !$operation instanceof MeasurementConversionOperation
            || !$value instanceof MeasurementValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Measurement Conversion operation');
        }

        $convertedValue = $this->measurementConverter->convert(
            $operation->getMeasurementFamilyCode(),
            $value->getUnitCode(),
            $operation->getTargetUnitCode(),
            $value->getValue(),
        );

        return new MeasurementValue($convertedValue, $operation->getTargetUnitCode());
    }

    public function supports(OperationInterface $operation, SourceValueInterface $value): bool
    {
        return $value instanceof MeasurementValue && $operation instanceof MeasurementConversionOperation;
    }
}
