<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\QualityHighlightsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectPendingItemIdentifiersQueryInterface;

class SynchronizeAttributesWithFranklin
{
    /** @var SelectPendingItemIdentifiersQueryInterface */
    private $pendingItemIdentifiersQuery;

    /** @var ApplyAttributeStructure */
    private $applyAttributeStructure;

    /** @var QualityHighlightsProviderInterface */
    private $qualityHighlightsProvider;

    public function __construct(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        ApplyAttributeStructure $applyAttributeStructure,
        QualityHighlightsProviderInterface $qualityHighlightsProvider
    ) {
        $this->pendingItemIdentifiersQuery = $pendingItemIdentifiersQuery;
        $this->applyAttributeStructure = $applyAttributeStructure;
        $this->qualityHighlightsProvider = $qualityHighlightsProvider;
    }

    public function synchronize(int $batchSize)
    {
        $this->synchronizeUpdatedAttributes($batchSize);
        $this->synchronizeDeletedAttributes($batchSize);
    }

    private function synchronizeUpdatedAttributes(int $batchSize)
    {
        $lastId = 0;
        while (true) {
            $attributeCodes = $this->pendingItemIdentifiersQuery->getUpdatedAttributeCodes($lastId, $batchSize);
            if (! empty($attributeCodes)) {
                $this->applyAttributeStructure->apply(array_values($attributeCodes));
            }

            if (count($attributeCodes) < $batchSize) {
                break;
            }

            //Cannot be done in 1 line because of a PHP warning
            $pendingItemIds = array_keys($attributeCodes);
            $lastId = end($pendingItemIds);
        }
    }

    private function synchronizeDeletedAttributes(int $batchSize)
    {
        $lastId = 0;
        while (true) {
            $attributeCodes = $this->pendingItemIdentifiersQuery->getDeletedAttributeCodes($lastId, $batchSize);
            if (! empty($attributeCodes)) {
                foreach ($attributeCodes as $attributeCode) {
                    $this->qualityHighlightsProvider->deleteAttribute($attributeCode);
                }
            }

            if (count($attributeCodes) < $batchSize) {
                break;
            }

            $pendingItemIds = array_keys($attributeCodes);
            $lastId = end($pendingItemIds);
        }
    }
}
