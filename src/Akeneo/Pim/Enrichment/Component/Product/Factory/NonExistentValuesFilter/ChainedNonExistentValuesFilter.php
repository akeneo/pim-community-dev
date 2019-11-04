<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use Akeneo\Pim\Enrichment\Component\Product\Factory\TransformRawValuesCollections;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * The implementation of this non existent filter uses a pivot format internally.
 * This pivot format helps to access the data by attribute type, in order to not iterate for each attribute type the whole raw value collection.
 *
 * We filter the data on this pivot format, and then re-transform it to raw values. We filter also null values and non existent channel or non activated locales.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChainedNonExistentValuesFilter implements ChainedNonExistentValuesFilterInterface
{
    /** @var iterable */
    private $nonExistentValueFilters;

    /** @var EmptyValuesCleaner */
    private $emptyValuesCleaner;

    /** @var TransformRawValuesCollections */
    private $transformRawValuesCollections;

    /** @var IdentifiableObjectRepositoryInterface*/
    private $localeRepository;

    /** @var IdentifiableObjectRepositoryInterface*/
    private $channelRepository;

    public function __construct(
        iterable $nonExistentValueFilters,
        EmptyValuesCleaner $emptyValuesCleaner,
        TransformRawValuesCollections $transformRawValuesCollections,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository
    ) {
        $this->nonExistentValueFilters = $nonExistentValueFilters;
        $this->emptyValuesCleaner = $emptyValuesCleaner;
        $this->transformRawValuesCollections = $transformRawValuesCollections;
        $this->localeRepository = $localeRepository;
        $this->channelRepository = $channelRepository;
    }

    public function filterAll(array $rawValuesCollection): array
    {
        $rawValueCollectionsIndexedByType = $this->transformRawValuesCollections->toValueCollectionsIndexedByType($rawValuesCollection);

        $onGoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType($rawValueCollectionsIndexedByType);

        /** @var OnGoingFilteredRawValues $result */
        $result = array_reduce(
            $this->iterableToArray($this->nonExistentValueFilters),
            function (OnGoingFilteredRawValues $onGoingFilteredRawValues, NonExistentValuesFilter $obsoleteValuesFilter): OnGoingFilteredRawValues {
                return $obsoleteValuesFilter->filter($onGoingFilteredRawValues);
            },
            $onGoingFilteredRawValues
        );

        $filteredRawValuesCollectionIndexedByType = $result->addFilteredValuesIndexedByType($result->nonFilteredRawValuesCollectionIndexedByType());
        $filteredRawValuesCollection = $this->emptyValuesCleaner->cleanAllValues($filteredRawValuesCollectionIndexedByType->toRawValueCollection());
        $filteredRawValuesCollection = $this->removeValuesWithNonExistentLocaleChannel($filteredRawValuesCollection);
        $filteredRawValuesCollection = $this->addIdentifiersWithOnlyUnknownAttributes($rawValuesCollection, $filteredRawValuesCollection);

        return $filteredRawValuesCollection;
    }

    private function iterableToArray(iterable $iterable): array
    {
        $array = [];
        array_push($array, ...$iterable);

        return $array;
    }

    /**
     * The pivot format indexes per attribute the values in the products. If the only attribute in the product does not exist, data about this product is lost in the pivot format.
     *
     * The goal of this function is to add the data about this product (empty raw values) in the final result.
     */
    private function addIdentifiersWithOnlyUnknownAttributes(array $rawValuesCollection, array $filteredRawValuesCollection): array
    {
        $emptyRawValuesCollection = array_fill_keys(array_keys($rawValuesCollection), []);

        return $filteredRawValuesCollection + $emptyRawValuesCollection;
    }

    private function removeValuesWithNonExistentLocaleChannel(array $rawValuesCollection)
    {
        $filteredRawValuesCollection = [];

        foreach ($rawValuesCollection as $identifier => $rawValues) {
            foreach ($rawValues as $attributeCode => $channelValues) {
                foreach ($channelValues as $channelCode => $localeValues) {
                    $channel = '<all_channels>' !== $channelCode ? $this->channelRepository->findOneByIdentifier($channelCode) : null;

                    foreach ($localeValues as $localeCode => $data) {
                        $locale = '<all_locales>' !== $localeCode ? $this->localeRepository->findOneByIdentifier($localeCode) : null;

                        if (
                            ($channelCode === '<all_channels>' || $channel !== null) &&
                            ($localeCode === '<all_locales>' || ($locale !== null && $locale->isActivated()))
                        ) {
                            $filteredRawValuesCollection[$identifier][$attributeCode][$channelCode][$localeCode] = $data;
                        }
                    }
                }
            }
        }

        return $filteredRawValuesCollection;
    }
}
