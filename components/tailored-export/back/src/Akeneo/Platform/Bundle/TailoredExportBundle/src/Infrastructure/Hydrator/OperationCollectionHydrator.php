<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Hydrator;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\DefaultValueOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\MeasurementConversionOperation;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\OperationInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\ReplacementOperation;

class OperationCollectionHydrator
{
    public function createAttributeOperationCollection(
        array $normalizedOperations,
        Attribute $attribute
    ): OperationCollection {
        return OperationCollection::create(array_filter(
            array_map(
                function (array $normalizedOperation) use ($attribute) {
                    switch ($normalizedOperation['type']) {
                        case 'measurement_conversion':
                            return new MeasurementConversionOperation(
                                $attribute->metricFamily(),
                                $normalizedOperation['target_unit_code'],
                            );
                        default:
                            return $this->createCommonOperation($normalizedOperation);
                    }
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
        switch ($normalizedOperation['type']) {
            case 'replacement':
                return new ReplacementOperation($normalizedOperation['mapping']);
            case 'default_value':
                return new DefaultValueOperation($normalizedOperation['value']);
            case 'clean_html_tags':
                return new CleanHTMLTagsOperation();
            default:
                return null;
        }
    }
}
