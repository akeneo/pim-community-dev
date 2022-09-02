<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Hydrator;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\DefaultValueOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\MeasurementConversionOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\MeasurementRoundingOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\OperationInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\ReplacementOperation;

class OperationCollectionHydrator
{
    public function createAttributeOperationCollection(
        array $normalizedOperations,
        Attribute $attribute,
    ): OperationCollection {
        return OperationCollection::create(array_filter(
            array_map(
                fn (array $normalizedOperation) => match ($normalizedOperation['type']) {
                    'measurement_conversion' => new MeasurementConversionOperation(
                        $attribute->metricFamily(),
                        $normalizedOperation['target_unit_code'],
                    ),
                    'measurement_rounding' => new MeasurementRoundingOperation(
                        $normalizedOperation['rounding_type'],
                        $normalizedOperation['precision'],
                    ),
                    default => $this->createCommonOperation($normalizedOperation),
                },
                $normalizedOperations,
            ),
        ));
    }

    public function createPropertyOperationCollection(array $normalizedOperations): OperationCollection
    {
        return OperationCollection::create(array_filter(
            array_map(
                fn (array $operation) => $this->createCommonOperation($operation),
                $normalizedOperations,
            ),
        ));
    }

    public function createAssociationTypeOperationCollection(array $normalizedOperations): OperationCollection
    {
        return OperationCollection::create(array_filter(
            array_map(
                fn (array $operation) => $this->createCommonOperation($operation),
                $normalizedOperations,
            ),
        ));
    }

    private function createCommonOperation(array $normalizedOperation): ?OperationInterface
    {
        return match ($normalizedOperation['type']) {
            'replacement' => new ReplacementOperation($normalizedOperation['mapping']),
            'default_value' => new DefaultValueOperation($normalizedOperation['value']),
            'clean_html_tags' => new CleanHTMLTagsOperation(),
            default => null,
        };
    }
}
