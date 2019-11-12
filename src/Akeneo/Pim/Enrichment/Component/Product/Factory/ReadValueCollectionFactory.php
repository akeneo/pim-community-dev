<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;

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

    public function __construct(
        ValueFactory $valueFactory,
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedNonExistentValuesFilter
    ) {
        $this->valueFactory = $valueFactory;
        $this->getAttributeByCodes = $getAttributeByCodes;
        $this->chainedNonExistentValuesFilter = $chainedNonExistentValuesFilter;
    }

    public function createFromStorageFormat(array $rawValues): ReadValueCollection
    {
        $notUsedIdentifier = 'not_used_identifier';

        return $this->createMultipleFromStorageFormat([$notUsedIdentifier => $rawValues])[$notUsedIdentifier];
    }

    /**
     * @param array $rawValueCollections
     * @return ReadValueCollection[]
     */
    public function createMultipleFromStorageFormat(array $rawValueCollections): array
    {
        $filteredRawValuesCollection = $this->chainedNonExistentValuesFilter->filterAll($rawValueCollections);

        return $this->createMultipleFromStorageFormatWithoutFilteringInconsistentData($filteredRawValuesCollection);
    }

    /**
     * Big warning: this method does not filter inconsistent data. For example, if a locale is removed,
     * the values about this locale will be present in the value collection, despite this one does not exist anymore.
     *
     * You should carefully use this method if you know that the inconsistency does not matter, as it's
     * the case for the Elasticsearch indexation for example. You should not use it if you expose it publicly,
     * such as in the API and very probably in the UI.
     * It is mainly skipped for performance purpose: you can get until a performance gain of 30%
     * to hydrate the values, which is not negligible.
     *
     * @param array $rawValueCollections
     * @return ReadValueCollection[]
     */
    public function createMultipleFromStorageFormatWithoutFilteringInconsistentData(array $rawValueCollections): array
    {
        $entities = [];
        $attributes = $this->getAttributesUsedByProducts($rawValueCollections);

        foreach ($rawValueCollections as $productIdentifier => $valueCollection) {
            $values = [];

            foreach ($valueCollection as $attributeCode => $channelRawValue) {
                if (!array_key_exists($attributeCode, $attributes)) {
                    continue;
                }

                $attribute = $attributes[$attributeCode];
                foreach ($channelRawValue as $channelCode => $localeRawValue) {
                    if ('<all_channels>' === $channelCode) {
                        $channelCode = null;
                    }

                    foreach ($localeRawValue as $localeCode => $data) {
                        if ('<all_locales>' === $localeCode) {
                            $localeCode = null;
                        }

                        $values[] = $this->valueFactory->createWithoutCheckingData(
                            $attribute,
                            $channelCode,
                            $localeCode,
                            $data
                        );
                    }
                }
            }

            $entities[$productIdentifier] = new ReadValueCollection($values);
        }

        return $entities;
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
}
