<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Infrastructure\Hydrator;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\AssociationType;
use Akeneo\Platform\Syndication\Application\Common\Column\Column;
use Akeneo\Platform\Syndication\Application\Common\Column\ColumnCollection;
use Akeneo\Platform\Syndication\Application\Common\Source\AssociationTypeSource;
use Akeneo\Platform\Syndication\Application\Common\Source\AttributeSource;
use Akeneo\Platform\Syndication\Application\Common\Source\PropertySource;
use Akeneo\Platform\Syndication\Application\Common\Source\SourceCollection;
use Akeneo\Platform\Syndication\Application\Common\Source\StaticSource;
use Akeneo\Platform\Syndication\Application\Common\Target\BooleanTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\MeasurementTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\NumberTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\PriceTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\StringCollectionTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\StringTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\Common\Target\UrlTarget;

class ColumnCollectionHydrator
{
    private SelectionHydrator $selectionHydrator;
    private FormatHydrator $formatHydrator;
    private OperationCollectionHydrator $operationCollectionHydrator;

    public function __construct(
        SelectionHydrator $selectionHydrator,
        FormatHydrator $formatHydrator,
        OperationCollectionHydrator $operationCollectionHydrator
    ) {
        $this->selectionHydrator = $selectionHydrator;
        $this->formatHydrator = $formatHydrator;
        $this->operationCollectionHydrator = $operationCollectionHydrator;
    }

    public function hydrate(array $columns, array $indexedAttributes, array $indexedAssociationTypes): ColumnCollection
    {
        $columnCollection = array_map(
            fn ($column) => new Column(
                $this->hydrateTarget($column['target']),
                $this->hydrateSourceCollection(
                    $column['sources'],
                    $indexedAttributes,
                    $indexedAssociationTypes,
                ),
                $this->formatHydrator->hydrate($column['format']),
            ),
            $columns,
        );

        return ColumnCollection::create($columnCollection);
    }

    private function hydrateSourceCollection(
        array $sources,
        array $indexedAttributes,
        array $indexedAssociationTypes
    ): SourceCollection {
        $sourceCollection = array_map(function ($source) use ($indexedAttributes, $indexedAssociationTypes) {
            if (AttributeSource::TYPE === $source['type']) {
                $attribute = $indexedAttributes[$source['code']] ?? null;

                if (null === $attribute) {
                    throw new \InvalidArgumentException(sprintf('The attribute "%s" does not exist', $source['code']));
                }

                $selection = $this->selectionHydrator->createAttributeSelection($source['selection'], $attribute);
                $operations = $this->operationCollectionHydrator->createAttributeOperationCollection($source['operations'], $attribute);

                return new AttributeSource(
                    $source['uuid'],
                    $attribute->type(),
                    $source['code'],
                    $source['channel'],
                    $source['locale'],
                    $operations,
                    $selection,
                );
            } elseif (StaticSource::TYPE === $source['type']) {
                $selection = $this->selectionHydrator->createStaticSelection($source['selection'], $source['code']);
                $operations = $this->operationCollectionHydrator->createStaticOperationCollection($source['operations']);

                return new StaticSource(
                    $source['uuid'],
                    $source['code'],
                    $source['value'],
                    $operations,
                    $selection,
                );
            } elseif (PropertySource::TYPE === $source['type']) {
                $selection = $this->selectionHydrator->createPropertySelection($source['selection'], $source['code']);
                $operations = $this->operationCollectionHydrator->createPropertyOperationCollection($source['operations']);

                return new PropertySource(
                    $source['uuid'],
                    $source['code'],
                    $source['channel'],
                    $source['locale'],
                    $operations,
                    $selection,
                );
            } elseif (AssociationTypeSource::TYPE === $source['type']) {
                $associationType = $indexedAssociationTypes[$source['code']] ?? null;
                if (!$associationType instanceof AssociationType) {
                    throw new \InvalidArgumentException(sprintf('The association type "%s" does not exist', $source['code']));
                }

                $selection = $this->selectionHydrator->createAssociationSelection($source['selection'], $associationType);
                $operations = $this->operationCollectionHydrator->createAssociationTypeOperationCollection($source['operations']);

                return new AssociationTypeSource(
                    $source['uuid'],
                    $source['code'],
                    $associationType->isQuantified(),
                    $operations,
                    $selection,
                );
            }

            throw new \InvalidArgumentException(sprintf('Unsupported source type "%s"', $source['type']));
        }, $sources);

        return SourceCollection::create($sourceCollection);
    }

    private function hydrateTarget(array $target): Target
    {
        switch ($target['type']) {
            case 'string':
            case 'limited_string': // Should have a dedicated target
                return new StringTarget(
                    $target['name'],
                    'string',
                    $target['required'] === '1',
                );
            case 'url':
                return new UrlTarget(
                    $target['name'],
                    'url',
                    $target['required'] === '1',
                );
            case 'boolean':
                return new BooleanTarget(
                    $target['name'],
                    'boolean',
                    $target['required'] === '1',
                );
            case 'number':
                return new NumberTarget(
                    $target['name'],
                    'number',
                    $target['required'] === '1',
                );
            case 'measurement':
                return new MeasurementTarget(
                    $target['name'],
                    'measurement',
                    $target['required'] === '1'
                );
            case 'string_collection':
                return new StringCollectionTarget(
                    $target['name'],
                    'string_collection',
                    $target['required'] === '1'
                );
            case 'price':
                return new PriceTarget(
                    $target['name'],
                    'price',
                    $target['required'] === '1'
                );

            default:
                throw new \InvalidArgumentException(sprintf('Unsupported target type "%s"', $target['type']));
        }
    }
}
