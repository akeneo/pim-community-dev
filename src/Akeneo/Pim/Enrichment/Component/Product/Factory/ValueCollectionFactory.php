<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner\ChainedEmptyValuesCleanerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner\OnGoingCleanedRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributeByCodes;
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
class ValueCollectionFactory implements ValueCollectionFactoryInterface
{
    /** @var ValueFactory */
    private $valueFactory;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var LoggerInterface */
    private $logger;

    /** @var GetAttributeByCodes */
    private $getAttributeByCodes;

    /** @var ChainedNonExistentValuesFilterInterface */
    private $chainedObsoleteValueFilter;

    /** @var ChainedEmptyValuesCleanerInterface */
    private $chainedEmptyValuesCleaner;

    public function __construct(
        ValueFactory $valueFactory,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        LoggerInterface $logger,
        GetAttributeByCodes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter,
        ChainedEmptyValuesCleanerInterface $chainedEmptyValuesCleaner
    ) {
        $this->valueFactory = $valueFactory;
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
        $this->getAttributeByCodes = $getAttributeByCodes;
        $this->chainedObsoleteValueFilter = $chainedObsoleteValueFilter;
        $this->chainedEmptyValuesCleaner = $chainedEmptyValuesCleaner;
    }

    /**
     * {@inheritdoc}
     *
     * Raw values that correspond to a non existing attribute (that was deleted
     * for instance) are NOT loaded.
     *
     * @see \Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product\ProductValuesNormalizer.php
     *
     * @param array $rawValues
     *
     * @return ValueCollectionInterface
     */
    public function createFromStorageFormat(array $rawValues, ?string $identifier = null)
    {
        $id = $identifier ?? uniqid();

        return $this->createMultipleFromStorageFormat([$id => $rawValues])[$id];
    }

    public function createMultipleFromStorageFormat(array $rawValueCollections): array
    {
        $rawValueCollectionsIndexedByType = $this->sortRawValueCollectionsToValueCollectionsIndexedByType($rawValueCollections);
        $entities = [];

        if (empty($rawValueCollectionsIndexedByType)) {
            foreach (array_keys($rawValueCollections) as $identifier) {
                $entities[$identifier] = new ValueCollection([]);
            }

            return $entities;
        }

        $filtered = $this->chainedObsoleteValueFilter->filterAll(
            OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType($rawValueCollectionsIndexedByType)
        );

        $cleaned = $this->chainedEmptyValuesCleaner->cleanAll(
            OnGoingCleanedRawValues::fromNonCleanedValuesCollectionIndexedByType($filtered->filteredRawValuesCollectionIndexedByType())
        );

        $entities = $this->createValues($cleaned->toRawValueCollection());

        $identifiersWithOnlyUnknownAttributes = array_diff(array_keys($rawValueCollections), array_keys($entities));

        foreach ($identifiersWithOnlyUnknownAttributes as $identifier) {
            $entities[$identifier] = new ValueCollection([]);
        }

        return $entities;
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

        $attributes = $this->getAttributeByCodes->forCodes($attributeCodes);

        if (empty($attributes)) {
            return [];
        }

        $codesToTypes = [];

        foreach ($attributes as $attribute) {
            $codesToTypes[$attribute->code()]= $attribute->type();
        }

        $typesToValues = [];

        foreach ($rawValueCollections as $productIdentifier => $rawValues) {
            foreach ($rawValues as $attributeCode => $values) {
                if (isset($codesToTypes[$attributeCode])) {
                    $typesToValues[$codesToTypes[$attributeCode]][$attributeCode][] = [
                        'identifier' => $productIdentifier,
                        'values' => $values,
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
                            //TODO: Embed that responsability inside the cleaner
                            $value = $this->valueFactory->create($attribute, $channelCode, $localeCode, $data, true);
                            $productData = $value->getData();
                            $isEmpty = (
                                null === $productData ||
                                (is_string($productData) && '' === trim($productData))  ||
                                (is_array($productData) && 0 === count($productData))
                            );
                            if (!$isEmpty) {
                                $values[] = $value;
                            }
                        } catch (InvalidAttributeException $e) {
                            $this->logger->warning(
                                sprintf(
                                    'Tried to load a product value with an invalid attribute "%s". %s',
                                    $attributeCode,
                                    $e->getMessage()
                                )
                            );
                        } catch (InvalidPropertyException $e) {
                            $this->logger->warning(
                                sprintf(
                                    'Tried to load a product value with the property "%s" that does not exist.',
                                    $e->getPropertyValue()
                                )
                            );
                        } catch (InvalidPropertyTypeException $e) {
                            $this->logger->warning(
                                sprintf(
                                    'Tried to load a product value for attribute "%s" that does not have the ' .
                                    'good type in database.',
                                    $attribute->getCode()
                                )
                            );
                            $values[] = $this->valueFactory->create($attribute, $channelCode, $localeCode, null);
                        }
                    }
                }
            }

            $entities[$productIdentifier] = new ValueCollection($values);
        }

        return $entities;
    }
}
