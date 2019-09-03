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

class SynchronizeFamiliesWithFranklin
{
    /** @var SelectPendingItemIdentifiersQueryInterface */
    private $pendingItemIdentifiersQuery;

    /** @var QualityHighlightsProviderInterface */
    private $qualityHighlightsProvider;

    public function __construct(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider
    ) {
        $this->pendingItemIdentifiersQuery = $pendingItemIdentifiersQuery;
        $this->qualityHighlightsProvider = $qualityHighlightsProvider;
    }

    public function synchronize(int $batchSize): void
    {
        $this->synchronizeUpdatedFamilies($batchSize);
        $this->synchronizeDeletedFamilies($batchSize);
    }

    private function synchronizeUpdatedFamilies(int $batchSize): void
    {
        $lastId = 0;
        while (true) {
            $familyCodes = $this->pendingItemIdentifiersQuery->getUpdatedFamilyCodes($lastId, $batchSize);
            if (! empty($familyCodes)) {
                $this->qualityHighlightsProvider->applyFamilies(array_values($familyCodes));
            }

            if (count($familyCodes) < $batchSize) {
                break;
            }

            //Cannot be done in 1 line because of a PHP warning
            $pendingItemIds = array_keys($familyCodes);
            $lastId = end($pendingItemIds);
        }
    }

    private function synchronizeDeletedFamilies(int $batchSize)
    {
        $lastId = 0;
        while (true) {
            $familyCodes = $this->pendingItemIdentifiersQuery->getDeletedFamilyCodes($lastId, $batchSize);
            if (! empty($familyCodes)) {
                foreach ($familyCodes as $familyCode) {
                    $this->qualityHighlightsProvider->deleteFamily($familyCode);
                }
            }

            if (count($familyCodes) < $batchSize) {
                break;
            }

            $pendingItemIds = array_keys($familyCodes);
            $lastId = end($pendingItemIds);
        }
    }
}
