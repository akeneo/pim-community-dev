<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
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
class ReadValueCollectionFactory
{
    /** @var ValueFactory */
    private $valueFactory;

    /** @var GetAttributes */
    private $getAttributeByCodes;

    /** @var ChainedNonExistentValuesFilterInterface */
    private $chainedNonExistentValuesFilter;

    // TODO merge master: property $logger should be nullable
    /** @var LoggerInterface|null */
    private $logger;

    // TODO merge master: require LoggerInterface argument
    public function __construct(
        ValueFactory $valueFactory,
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedNonExistentValuesFilter,
        LoggerInterface $logger = null
    ) {
        $this->valueFactory = $valueFactory;
        $this->getAttributeByCodes = $getAttributeByCodes;
        $this->chainedNonExistentValuesFilter = $chainedNonExistentValuesFilter;
        $this->logger = $logger;
    }

    public function createFromStorageFormat(array $rawValues): ReadValueCollection
    {
        $notUsedIdentifier = 'not_used_identifier';

        return $this->createMultipleFromStorageFormat([$notUsedIdentifier => $rawValues])[$notUsedIdentifier];
    }

    public function createMultipleFromStorageFormat(array $rawValueCollections): array
    {
        $filteredRawValuesCollection = $this->chainedNonExistentValuesFilter->filterAll($rawValueCollections);
        $valueCollections = $this->createValues($filteredRawValuesCollection);

        return $valueCollections;
    }

    private function getAttributesUsedByProducts(array $rawValueCollections): array
    {
        $attributeCodes = [];

        foreach ($rawValueCollections as $productIdentifier => $rawValues) {
            foreach (\array_keys($rawValues) as $attributeCode) {
                $attributeCodes[] = (string) $attributeCode;
            }
        }

        $attributes = $this->getAttributeByCodes->forCodes(\array_unique($attributeCodes));

        return $attributes;
    }

    private function createValues(array $rawValueCollections): array
    {
        $entities = [];
        $attributes = $this->getAttributesUsedByProducts($rawValueCollections);

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

                        // TODO merge master: DO NOT MERGE, remove the try/catch and revert to createWithoutCheckingData
                        try {
                            $values[] = $this->valueFactory->createByCheckingData(
                                $attribute,
                                $channelCode,
                                $localeCode,
                                $data
                            );
                        } catch (\TypeError | InvalidPropertyTypeException | InvalidPropertyException $ex) {
                            if (null !== $this->logger) {
                                $this->logger->notice(
                                    sprintf(
                                        'Tried to load a product value for attribute "%s" that does not have the '.
                                        'expected type in database.',
                                        $attribute->code()
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
