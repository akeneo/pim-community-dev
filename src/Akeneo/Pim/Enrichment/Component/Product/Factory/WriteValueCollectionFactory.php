<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Create a product value collection.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WriteValueCollectionFactory
{
    /** @var ValueFactory */
    private $valueFactory;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var LoggerInterface */
    private $logger;

    /** @var GetAttributes */
    private $getAttributeByCodes;

    /** @var ChainedNonExistentValuesFilterInterface */
    private $chainedObsoleteValueFilter;

    /** @var EmptyValuesCleaner */
    private $emptyValuesCleaner;

    public function __construct(
        ValueFactory $valueFactory,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        LoggerInterface $logger,
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter,
        EmptyValuesCleaner $emptyValuesCleaner
    ) {
        $this->valueFactory = $valueFactory;
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
        $this->getAttributeByCodes = $getAttributeByCodes;
        $this->chainedObsoleteValueFilter = $chainedObsoleteValueFilter;
        $this->emptyValuesCleaner = $emptyValuesCleaner;
    }

    public function createFromStorageFormat(array $rawValues): WriteValueCollection
    {
        $notUsedIdentifier = 'not_used_identifier';

        return $this->createMultipleFromStorageFormat([$notUsedIdentifier => $rawValues])[$notUsedIdentifier];
    }

    public function createMultipleFromStorageFormat(array $rawValueCollections): array
    {
        $rawValueCollectionsIndexedByType = $this->sortRawValueCollectionsToValueCollectionsIndexedByType($rawValueCollections);
        $valueCollections = [];

        if (empty($rawValueCollectionsIndexedByType)) {
            foreach (array_keys($rawValueCollections) as $identifier) {
                $valueCollections[$identifier] = new WriteValueCollection([]);
            }

            return $valueCollections;
        }

        $filtered = $this->chainedObsoleteValueFilter->filterAll(
            OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType($rawValueCollectionsIndexedByType)
        );

        $rawValueCollection = $filtered->toRawValueCollection();

        $cleanRawValueCollection = $this->emptyValuesCleaner->cleanAllValues($rawValueCollection);

        $valueCollections = $this->createValues($cleanRawValueCollection);

        $identifiersWithOnlyUnknownAttributes = array_diff(array_keys($rawValueCollections), array_keys($valueCollections));

        foreach ($identifiersWithOnlyUnknownAttributes as $identifier) {
            $valueCollections[$identifier] = new WriteValueCollection([]);
        }

        return $valueCollections;
    }

    private function sortRawValueCollectionsToValueCollectionsIndexedByType(array $rawValueCollections): array
    {
        $attributeCodes = [];
        $attributeCodesPerProduct = [];

        foreach ($rawValueCollections as $productIdentifier => $rawValues) {
            foreach (array_keys($rawValues) as $attributeCode) {
                $attributeCodes[] = (string) $attributeCode;
                $attributeCodesPerProduct[$productIdentifier][] = $attributeCode;
            }
        }

        $attributes = array_filter($this->getAttributeByCodes->forCodes(array_values(array_unique($attributeCodes))));

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

    private function createValues(array $rawValueCollections): array
    {
        $entities = [];

        foreach ($rawValueCollections as $productIdentifier => $valueCollection) {
            $values = [];

            foreach ($valueCollection as $attributeCode => $channelRawValue) {
                $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

                foreach ($channelRawValue as $channelCode => $localeRawValue) {
                    if ('<all_channels>' === $channelCode) {
                        $channelCode = null;
                    }

                    foreach ($localeRawValue as $localeCode => $data) {
                        if ('<all_locales>' === $localeCode) {
                            $localeCode = null;
                        }

                        try {
                            $values[] = $this->valueFactory->create($attribute, $channelCode, $localeCode, $data, true);
                        } catch (InvalidAttributeException $e) {
                            $this->logger->notice(
                                sprintf(
                                    'Tried to load a product value with an invalid attribute "%s". %s',
                                    $attributeCode,
                                    $e->getMessage()
                                )
                            );
                        } catch (InvalidPropertyException $e) {
                            $this->logger->notice(
                                sprintf(
                                    'Tried to load a product value with the property "%s" that does not exist.',
                                    $e->getPropertyValue()
                                )
                            );
                        } catch (\TypeError | InvalidPropertyTypeException $e) {
                            $this->logger->notice(
                                sprintf(
                                    'Tried to load a product value for attribute "%s" that does not have the ' .
                                    'expected type in database.',
                                    $attribute->getCode()
                                )
                            );
                        }
                    }
                }
            }

            $entities[$productIdentifier] = new WriteValueCollection($values);
        }

        return $entities;
    }
}
