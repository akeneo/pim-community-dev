<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChainedNonExistentValuesFilter implements ChainedNonExistentValuesFilterInterface
{
    /** @var iterable */
    private $obsoleteValueFilters;

    public function __construct(iterable $obsoleteValueFilters)
    {
        $this->obsoleteValueFilters = $obsoleteValueFilters;
    }

    public function filterAll(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        /** @var OnGoingFilteredRawValues $result */
        $result = array_reduce(
            $this->iterableToArray($this->obsoleteValueFilters),
            function (OnGoingFilteredRawValues $onGoingFilteredRawValues, NonExistentValuesFilter $obsoleteValuesFilter) {
                return $obsoleteValuesFilter->filter($onGoingFilteredRawValues);
            },
            $onGoingFilteredRawValues
        );

        /** Takes the rest as it is */
        return $result->addFilteredValuesIndexedByType($result->nonFilteredRawValuesCollectionIndexedByType());
    }

    private function iterableToArray(iterable $iterable): array
    {
        $array = [];
        array_push($array, ...$iterable);

        return $array;
    }
}
