<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use Akeneo\Pim\Enrichment\Component\Product\Factory\TransformRawValuesCollections;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

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
    public function __construct(
        private iterable $nonExistentValueFilters,
        private NonExistentChannelLocaleValuesFilter $nonExistentChannelLocaleValuesFilter,
        private EmptyValuesCleaner $emptyValuesCleaner,
        private TransformRawValuesCollections $transformRawValuesCollections
    ) {
    }

    public function filterAll(array $rawValuesCollection): array
    {
        $rawValueCollectionsIndexedByType = $this->transformRawValuesCollections->toValueCollectionsIndexedByType($rawValuesCollection);
        $onGoingFilteredRawValues = OnGoingFilteredRawValues::fromNonFilteredValuesCollectionIndexedByType($rawValueCollectionsIndexedByType);

        /** @var OnGoingFilteredRawValues $result */
        $result = \array_reduce(
            $this->iterableToArray($this->nonExistentValueFilters),
            function (OnGoingFilteredRawValues $onGoingFilteredRawValues, NonExistentValuesFilter $obsoleteValuesFilter): OnGoingFilteredRawValues {
                try {
                    return $obsoleteValuesFilter->filter($onGoingFilteredRawValues);
                } catch (\TypeError | InvalidPropertyTypeException $exception) {
                    return $onGoingFilteredRawValues;
                }
            },
            $onGoingFilteredRawValues
        );

        $onGoingFilteredRawValuesForChannelAndLocale = new OnGoingFilteredRawValues(
            [],
            $result->filteredRawValuesCollectionIndexedByType() + $result->nonFilteredRawValuesCollectionIndexedByType()
        );
        $result = $this->nonExistentChannelLocaleValuesFilter->filter($onGoingFilteredRawValuesForChannelAndLocale);

        $filteredRawValuesCollectionIndexedByType = $result->addFilteredValuesIndexedByType($result->nonFilteredRawValuesCollectionIndexedByType());
        $filteredRawValuesCollection = $this->emptyValuesCleaner->cleanAllValues($filteredRawValuesCollectionIndexedByType->toRawValueCollection());

        return $this->addIdentifiersWithOnlyUnknownAttributes($rawValuesCollection, $filteredRawValuesCollection);
    }

    private function iterableToArray(iterable $iterable): array
    {
        return \is_array($iterable) ? $iterable : \iterator_to_array($iterable);
    }

    /**
     * The pivot format indexes per attribute the values in the products. If the only attribute in the product does not exist, data about this product is lost in the pivot format.
     *
     * The goal of this function is to add the data about this product (empty raw values) in the final result.
     */
    private function addIdentifiersWithOnlyUnknownAttributes(array $rawValuesCollection, array $filteredRawValuesCollection): array
    {
        $emptyRawValuesCollection = \array_fill_keys(\array_keys($rawValuesCollection), []);

        return $filteredRawValuesCollection + $emptyRawValuesCollection;
    }
}
