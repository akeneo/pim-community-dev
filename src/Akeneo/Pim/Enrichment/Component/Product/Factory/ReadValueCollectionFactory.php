<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReadValueCollectionFactory
{
    private ValueFactory $valueFactory;
    private GetAttributes $getAttributeByCodes;
    private ChainedNonExistentValuesFilterInterface $chainedNonExistentValuesFilter;
    private LoggerInterface $logger;

    public function __construct(
        ValueFactory $valueFactory,
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedNonExistentValuesFilter,
        LoggerInterface $logger
    ) {
        $this->valueFactory = $valueFactory;
        $this->getAttributeByCodes = $getAttributeByCodes;
        $this->chainedNonExistentValuesFilter = $chainedNonExistentValuesFilter;
        $this->logger = $logger;
    }

    public function createFromStorageFormat(array $rawValues): ReadValueCollection
    {
        $notUsedUuid = Uuid::uuid4()->toString();

        return $this->createMultipleFromStorageFormat([$notUsedUuid => $rawValues])[$notUsedUuid];
    }

    public function createMultipleFromStorageFormat(array $rawValueCollections): array
    {
        $filteredRawValuesCollection = $this->chainedNonExistentValuesFilter->filterAll($rawValueCollections);
        $attributes = $this->getAttributesUsedByProducts($rawValueCollections);
        $filteredRawValuesCollection = CleanLineBreaksInTextAttributes::cleanFromRawValuesFormat($filteredRawValuesCollection, $attributes);

        return $this->createValues($filteredRawValuesCollection, $attributes);
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

    private function createValues(array $rawValueCollections, array $attributes): array
    {
        $entities = [];
        foreach ($rawValueCollections as $productUuid => $valueCollection) {
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
                            $values[] = $this->valueFactory->createByCheckingData(
                                $attribute,
                                $channelCode,
                                $localeCode,
                                $data
                            );
                        } catch (\TypeError | InvalidPropertyTypeException | InvalidPropertyException $exception) {
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

            $entities[$productUuid] = new ReadValueCollection($values);
        }

        return $entities;
    }
}
