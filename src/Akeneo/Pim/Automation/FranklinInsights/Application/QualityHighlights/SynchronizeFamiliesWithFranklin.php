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
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Ramsey\Uuid\Uuid;

class SynchronizeFamiliesWithFranklin
{
    /** @var SelectPendingItemIdentifiersQueryInterface */
    private $pendingItemIdentifiersQuery;

    /** @var QualityHighlightsProviderInterface */
    private $qualityHighlightsProvider;

    /** @var PendingItemsRepositoryInterface */
    private $pendingItemsRepository;

    public function __construct(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository
    ) {
        $this->pendingItemIdentifiersQuery = $pendingItemIdentifiersQuery;
        $this->qualityHighlightsProvider = $qualityHighlightsProvider;
        $this->pendingItemsRepository = $pendingItemsRepository;
    }

    public function synchronize(Uuid $lockUUID, int $batchSize): void
    {
        $this->synchronizeUpdatedFamilies($lockUUID, $batchSize);
        $this->synchronizeDeletedFamilies($lockUUID, $batchSize);
    }

    private function synchronizeUpdatedFamilies(Uuid $lockUUID, int $batchSize): void
    {
        do {
            $familyCodes = $this->pendingItemIdentifiersQuery->getUpdatedFamilyCodes($lockUUID, $batchSize);
            if (! empty($familyCodes)) {
                $this->qualityHighlightsProvider->applyFamilies(array_values($familyCodes));
            }

            $this->pendingItemsRepository->removeUpdatedFamilies($familyCodes, $lockUUID);
        } while (count($familyCodes) >= $batchSize);
    }

    private function synchronizeDeletedFamilies(Uuid $lockUUID, int $batchSize)
    {
        do {
            $familyCodes = $this->pendingItemIdentifiersQuery->getDeletedFamilyCodes($lockUUID, $batchSize);
            if (! empty($familyCodes)) {
                foreach ($familyCodes as $familyCode) {
                    $this->qualityHighlightsProvider->deleteFamily($familyCode);
                }
            }

            $this->pendingItemsRepository->removeDeletedFamilies($familyCodes, $lockUUID);
        } while (count($familyCodes) >= $batchSize);
    }
}
