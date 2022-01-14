<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Infrastructure\Hydrator;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Platform\Syndication\Application\Common\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\DefaultValueOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\MeasurementConversionOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\MeasurementRoundingOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\OperationCollection;
use Akeneo\Platform\Syndication\Application\Common\Operation\OperationInterface;
use Akeneo\Platform\Syndication\Application\Common\Operation\ReplacementOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\String\ExtractOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\String\SplitOperation;

class OperationCollectionHydrator
{
    public function createAttributeOperationCollection(
        array $normalizedOperations,
        Attribute $attribute
    ): OperationCollection {
        return OperationCollection::create(array_filter(
            array_map(
                function (array $normalizedOperation) use ($attribute) {
                    return match ($normalizedOperation['type']) {
                        'measurement_conversion' => new MeasurementConversionOperation(
                            $attribute->metricFamily(),
                            $normalizedOperation['target_unit_code'],
                        ),
                        'measurement_rounding' => new MeasurementRoundingOperation(
                            $normalizedOperation['rounding_type'],
                            $normalizedOperation['precision'],
                        ),
                        default => $this->createCommonOperation($normalizedOperation),
                    };
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

    public function createStaticOperationCollection(array $normalizedOperations): OperationCollection
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
            case 'extract':
                return new ExtractOperation($normalizedOperation['regexp']);
            case 'split':
                return new SplitOperation($normalizedOperation['separator']);
            default:
                return null;
        }
    }
}
