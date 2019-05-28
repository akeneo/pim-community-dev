<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Read;

use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueCollectionFactory implements ValueCollectionFactoryInterface
{
    /** @var ReadValueFactory */
    private $valueFactory;

    /** @var GetAttributes */
    private $getAttributeByCodes;

    /** @var ChainedNonExistentValuesFilterInterface */
    private $chainedNonExistentValuesFilter;

    /** @var EmptyValuesCleaner */
    private $emptyValuesCleaner;

    public function __construct(
        ReadValueFactory $valueFactory,
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedNonExistentValuesFilter,
        EmptyValuesCleaner $emptyValuesCleaner
    ) {
        $this->valueFactory = $valueFactory;
        $this->getAttributeByCodes = $getAttributeByCodes;
        $this->chainedNonExistentValuesFilter = $chainedNonExistentValuesFilter;
        $this->emptyValuesCleaner = $emptyValuesCleaner;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromStorageFormat(array $rawValues)
    {
        $notUsedIdentifier = 'not_used_identifier';

        return $this->createMultipleFromStorageFormat([$notUsedIdentifier => $rawValues])[$notUsedIdentifier];
    }

    public function createMultipleFromStorageFormat(array $rawValueCollections): array
    {
        $attributes = $this->getAttributesUsedByProducts($rawValueCollections);

        $rawValueCollectionsIndexedByType = $this->sortRawValueCollectionsToValueCollectionsIndexedByType($rawValueCollections, $attributes);
        $valueCollections = [];

        if (empty($rawValueCollectionsIndexedByType)) {
            foreach (array_keys($rawValueCollections) as $identifier) {
                $valueCollections[$identifier] = new ReadValueCollection([]);
            }

            return $valueCollections;
        }

        $filtered = $this->chainedNonExistentValuesFilter->filterAll(
            OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType($rawValueCollectionsIndexedByType)
        );

        $rawValueCollection = $filtered->toRawValueCollection();

        $cleanRawValueCollection = $this->emptyValuesCleaner->cleanAllValues($rawValueCollection);

        $valueCollections = $this->createValues($cleanRawValueCollection, $attributes);

        $identifiersWithOnlyUnknownAttributes = array_diff(array_keys($rawValueCollections), array_keys($valueCollections));

        foreach ($identifiersWithOnlyUnknownAttributes as $identifier) {
            $valueCollections[$identifier] = new ReadValueCollection([]);
        }

        return $valueCollections;
    }

    private function getAttributesUsedByProducts(array $rawValueCollections): array
    {
        $attributeCodes = [];

        foreach ($rawValueCollections as $productIdentifier => $rawValues) {
            foreach (array_keys($rawValues) as $attributeCode) {
                $attributeCodes[] = (string) $attributeCode;
            }
        }

        $attributes = $this->getAttributeByCodes->forCodes(array_unique($attributeCodes));

        return $attributes;
    }

    private function sortRawValueCollectionsToValueCollectionsIndexedByType(array $rawValueCollections, array $attributes): array
    {
        if (empty($attributes)) {
            return [];
        }

        $attributesIndexedByCodes = [];

        foreach ($attributes as $attribute) {
            $attributesIndexedByCodes[$attribute->code()]['type'] = $attribute->type();
            $attributesIndexedByCodes[$attribute->code()]['properties'] = $attribute->properties();
        }

        $typesToValues = [];

        foreach ($rawValueCollections as $productIdentifier => $rawValues) {
            foreach ($rawValues as $attributeCode => $values) {
                if (isset($attributesIndexedByCodes[$attributeCode])) {
                    $typesToValues[$attributesIndexedByCodes[$attributeCode]['type']][$attributeCode][] = [
                        'identifier' => $productIdentifier,
                        'values' => $values,
                        'properties' => $attributesIndexedByCodes[$attributeCode]['properties']
                    ];
                }
            }
        }

        return $typesToValues;
    }

    private function createValues(array $rawValueCollections, array $attributes): array
    {
        $entities = [];

        $attributesIndexedByCode = [];
        foreach ($attributes as $attribute) {
            $attributesIndexedByCode[$attribute->code()] = $attribute;
        }

        foreach ($rawValueCollections as $productIdentifier => $valueCollection) {
            $values = [];

            foreach ($valueCollection as $attributeCode => $channelRawValue) {
                $attribute = $attributesIndexedByCode[$attributeCode];

                foreach ($channelRawValue as $channelCode => $localeRawValue) {
                    if ('<all_channels>' === $channelCode) {
                        $channelCode = null;
                    }

                    foreach ($localeRawValue as $localeCode => $data) {
                        if ('<all_locales>' === $localeCode) {
                            $localeCode = null;
                        }

                        $values[] = $this->valueFactory->create($attribute, $channelCode, $localeCode, $data);
                    }
                }
            }

            $entities[$productIdentifier] = new ReadValueCollection($values);
        }

        return $entities;
    }
}
