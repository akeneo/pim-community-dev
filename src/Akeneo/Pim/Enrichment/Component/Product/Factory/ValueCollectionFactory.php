<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\OnGoingFilteredRawValues;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
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
class ValueCollectionFactory implements ValueCollectionFactoryInterface
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
        $rawValueCollectionsIndexedByType = $this->sortRawValueCollectionsToValueCollectionsIndexedByType($rawValueCollections);
        $valueCollections = [];

        if (empty($rawValueCollectionsIndexedByType)) {
            foreach (array_keys($rawValueCollections) as $identifier) {
                $valueCollections[$identifier] = new ValueCollection([]);
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
            $valueCollections[$identifier] = new ValueCollection([]);
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

        $attributes = $this->getAttributeByCodes->forCodes($attributeCodes);

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
