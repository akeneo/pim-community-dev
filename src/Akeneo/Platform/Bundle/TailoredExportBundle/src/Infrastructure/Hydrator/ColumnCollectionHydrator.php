<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Hydrator;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\AssociationType;
use Akeneo\Platform\TailoredExport\Domain\Model\Column\Column;
use Akeneo\Platform\TailoredExport\Domain\Model\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Domain\Model\Source\AssociationTypeSource;
use Akeneo\Platform\TailoredExport\Domain\Model\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Domain\Model\Source\PropertySource;
use Akeneo\Platform\TailoredExport\Domain\Model\Source\SourceCollection;

class ColumnCollectionHydrator
{
    private SelectionHydrator $selectionHydrator;

    public function __construct(
        SelectionHydrator $selectionHydrator
    ) {
        $this->selectionHydrator = $selectionHydrator;
    }

    public function hydrate(array $columns, array $indexedAttributes, array $indexedAssociationTypes): ColumnCollection
    {
        $columnCollection = array_map(
            fn ($column) => new Column(
                $column['target'],
                $this->hydrateSourceCollection($column['sources'], $indexedAttributes, $indexedAssociationTypes)
            ),
            $columns
        );

        return ColumnCollection::create($columnCollection);
    }

    private function hydrateSourceCollection(
        array $sources,
        array $indexedAttributes,
        array $indexedAssociationTypes
    ): SourceCollection {
        $sourceCollection = array_map(function ($source) use ($indexedAttributes, $indexedAssociationTypes) {
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
            } elseif (PropertySource::TYPE === $source['type']) {
                $selection = $this->selectionHydrator->createPropertySelection($source['selection'], $source['code']);

                return new PropertySource(
                    $source['code'],
                    $operations,
                    $selection
                );
            } elseif (AssociationTypeSource::TYPE === $source['type']) {
                $associationType = $indexedAssociationTypes[$source['code']] ?? null;
                if (!$associationType instanceof AssociationType) {
                    throw new \InvalidArgumentException(sprintf('The association type "%s" does not exist', $source['code']));
                }

                $selection = $this->selectionHydrator->createAssociationSelection($source['selection'], $associationType);

                return new AssociationTypeSource(
                    $source['code'],
                    $associationType->isQuantified(),
                    $operations,
                    $selection
                );
            }

            throw new \InvalidArgumentException(sprintf('Unsupported source type "%s"', $source['type']));
        }, $sources);

        return SourceCollection::create($sourceCollection);
    }
}
