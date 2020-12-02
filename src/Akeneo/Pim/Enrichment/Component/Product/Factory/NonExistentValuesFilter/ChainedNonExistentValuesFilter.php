<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChainedNonExistentValuesFilter implements ChainedNonExistentValuesFilterInterface
{
    /** @var iterable */
    private $nonExistentValueFilters;

    public function __construct(iterable $nonExistentValueFilters)
    {
        $this->nonExistentValueFilters = $nonExistentValueFilters;
    }

    public function filterAll(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $result = array_reduce(
            $this->iterableToArray($this->nonExistentValueFilters),
            function (OnGoingFilteredRawValues $onGoingFilteredRawValues, NonExistentValuesFilter $obsoleteValuesFilter): OnGoingFilteredRawValues {
                try {
                    return $obsoleteValuesFilter->filter($onGoingFilteredRawValues);
                } catch (\TypeError | InvalidPropertyTypeException $ex) {
                    return $onGoingFilteredRawValues;
                }
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
