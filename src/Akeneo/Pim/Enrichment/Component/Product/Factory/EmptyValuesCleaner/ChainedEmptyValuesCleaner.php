<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChainedEmptyValuesCleaner implements ChainedEmptyValuesCleanerInterface
{
    /** @var iterable */
    private $emptyValuesCleaners;

    public function __construct(iterable $emptyValuesCleaners)
    {
        $this->emptyValuesCleaners = $emptyValuesCleaners;
    }

    public function cleanAll(OnGoingCleanedRawValues $onGoingCleanedRawValues): OnGoingCleanedRawValues
    {
        $result = array_reduce(
            $this->iterableToArray($this->emptyValuesCleaners),
            function (OnGoingCleanedRawValues $onGoingFilteredRawValues, EmptyValuesCleaner $emptyValuesCleaner): OnGoingCleanedRawValues {
                return $emptyValuesCleaner->clean($onGoingFilteredRawValues);
            },
            $onGoingCleanedRawValues
        );

        /** Takes the rest as it is */
        return $result->addCleanedValuesIndexedByType($result->nonCleanedRawValuesCollectionIndexedByType());
    }

    /**
     * There is no PHP native method to do that kind of transformation
     */
    private function iterableToArray(iterable $iterable): array
    {
        $array = [];
        array_push($array, ...$iterable);

        return $array;
    }
}
