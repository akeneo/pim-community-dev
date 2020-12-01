<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Read;

use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Psr\Log\LoggerInterface;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueCollectionFactory
{
    /** @var ReadValueFactory */
    private $valueFactory;

    /** @var GetAttributes */
    private $getAttributeByCodes;

    /** @var ChainedNonExistentValuesFilterInterface */
    private $chainedNonExistentValuesFilter;

    /** @var EmptyValuesCleaner */
    private $emptyValuesCleaner;

    // TODO merge master/4.0: property $logger should be nullable
    /** @var LoggerInterface|null */
    private $logger;

    // TODO merge master/4.0: require LoggerInterface argument
    public function __construct(
        ReadValueFactory $valueFactory,
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedNonExistentValuesFilter,
        EmptyValuesCleaner $emptyValuesCleaner,
        LoggerInterface $logger = null
    ) {
        $this->valueFactory = $valueFactory;
        $this->getAttributeByCodes = $getAttributeByCodes;
        $this->chainedNonExistentValuesFilter = $chainedNonExistentValuesFilter;
        $this->emptyValuesCleaner = $emptyValuesCleaner;
        $this->logger = $logger;
    }

    public function createFromStorageFormat(array $rawValues): ReadValueCollection
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

        $typesToValues = [];

        foreach ($rawValueCollections as $productIdentifier => $rawValues) {
            foreach ($rawValues as $attributeCode => $values) {
                if (isset($attributes[$attributeCode])) {
                    $type = $attributes[$attributeCode]->type();
                    $properties = $attributes[$attributeCode]->properties();

                    $typesToValues[$type][$attributeCode][] = [
                        'identifier' => $productIdentifier,
                        'values' => $values,
                        'properties' => $properties
                    ];
                }
            }
        }

        return $typesToValues;
    }

    private function createValues(array $rawValueCollections, array $attributes): array
    {
        $entities = [];

        foreach ($rawValueCollections as $productIdentifier => $valueCollection) {
            $values = [];

            foreach ($valueCollection as $attributeCode => $channelRawValue) {
                $attribute = $attributes[$attributeCode];

                foreach ($channelRawValue as $channelCode => $localeRawValue) {
                    if ('<all_channels>' === $channelCode) {
                        $channelCode = null;
                    }

                    foreach ($localeRawValue as $localeCode => $data) {
                        if ('<all_locales>' === $localeCode) {
                            $localeCode = null;
                        }

                        try {
                            $values[] = $this->valueFactory->create($attribute, $channelCode, $localeCode, $data);
                        } catch (InvalidPropertyException $e) {
                            // TODO merge master/4.0: remove check
                            if (null !== $this->logger) {
                                $this->logger->notice(
                                    sprintf(
                                        'Tried to load a product value with the property "%s" that does not exist.',
                                        $e->getPropertyValue()
                                    )
                                );
                            }
                        } catch (\TypeError | InvalidPropertyTypeException $e) {
                            // TODO merge master/4.0: remove check
                            if (null !== $this->logger) {
                                $this->logger->notice(
                                    sprintf(
                                        'Tried to load a product value for attribute "%s" that does not have the '.
                                        'expected type in database.',
                                        $attribute->getCode()
                                    )
                                );
                            }
                        }
                    }
                }
            }

            $entities[$productIdentifier] = new ReadValueCollection($values);
        }

        return $entities;
    }
}
