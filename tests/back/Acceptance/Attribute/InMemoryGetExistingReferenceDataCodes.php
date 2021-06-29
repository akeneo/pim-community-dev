<?php

declare(strict_types=1);

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Acceptance\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetExistingReferenceDataCodes;

final class InMemoryGetExistingReferenceDataCodes implements GetExistingReferenceDataCodes
{
    /** @var array */
    private $referenceDataCodesIndexedByReferenceDataName = [];

    public function fromReferenceDataNameAndCodes(string $referenceDataName, array $codes): array
    {
        if (!\array_key_exists($referenceDataName, $this->referenceDataCodesIndexedByReferenceDataName)) {
            return [];
        }

        return \array_values(
            \array_intersect($codes, $this->referenceDataCodesIndexedByReferenceDataName[$referenceDataName])
        );
    }

    public function add(string $referenceDataName, string $referenceDataCode): void
    {
        if (!\array_key_exists($referenceDataName, $this->referenceDataCodesIndexedByReferenceDataName)) {
            $this->referenceDataCodesIndexedByReferenceDataName[$referenceDataName] = [];
        }

        if (\in_array($referenceDataCode, $this->referenceDataCodesIndexedByReferenceDataName[$referenceDataName])) {
            return;
        }

        $this->referenceDataCodesIndexedByReferenceDataName[$referenceDataName][] = $referenceDataCode;
    }
}
