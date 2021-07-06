<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Hydrator;

use Akeneo\Platform\TailoredExport\Application\Query\Column\Column;
use Akeneo\Platform\TailoredExport\Application\Query\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Application\Query\Source\PropertySource;
use Akeneo\Platform\TailoredExport\Application\Query\Source\SourceCollection;

class ColumnCollectionHydrator
{
    private SelectionHydrator $selectionHydrator;

    public function __construct(
        SelectionHydrator $selectionHydrator
    ) {
        $this->selectionHydrator = $selectionHydrator;
    }

    public function hydrate(array $columns, array $indexedAttributes): ColumnCollection
    {
        $columnCollection = array_map(
            fn ($column) => new Column(
                $column['target'],
                $this->hydrateSourceCollection($column['sources'], $indexedAttributes)
            ),
            $columns
        );

        return ColumnCollection::create($columnCollection);
    }

    private function hydrateSourceCollection(array $sources, array $indexedAttributes): SourceCollection
    {
        $sourceCollection = array_map(function ($source) use ($indexedAttributes) {
            $operations = OperationCollection::createFromNormalized($source['operations']);

            if (AttributeSource::TYPE === $source['type']) {
                $attribute = $indexedAttributes[$source['code']] ?? null;

                if (null === $attribute) {
                    throw new \InvalidArgumentException(sprintf('The attribute "%s" does not exist', $source['code']));
                }

                $selection = $this->selectionHydrator->createAttributeSelection($source['selection'], $attribute);

                return new AttributeSource(
                    $attribute->type(),
                    $source['code'],
                    $source['channel'],
                    $source['locale'],
                    $operations,
                    $selection
                );
            } else {
                $selection = $this->selectionHydrator->createPropertySelection($source['selection'], $source['code']);

                return new PropertySource(
                    $source['code'],
                    $operations,
                    $selection
                );
            }
        }, $sources);

        return SourceCollection::create($sourceCollection);
    }
}
