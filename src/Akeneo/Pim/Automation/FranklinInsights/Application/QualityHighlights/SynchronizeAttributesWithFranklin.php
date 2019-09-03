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
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;

class SynchronizeAttributesWithFranklin
{
    /** @var SelectPendingItemIdentifiersQueryInterface */
    private $pendingItemIdentifiersQuery;

    /** @var ApplyAttributeStructure */
    private $applyAttributeStructure;

    /** @var QualityHighlightsProviderInterface */
    private $qualityHighlightsProvider;

    /** @var PendingItemsRepositoryInterface */
    private $pendingItemsRepository;

    public function __construct(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        ApplyAttributeStructure $applyAttributeStructure,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository
    ) {
        $this->pendingItemIdentifiersQuery = $pendingItemIdentifiersQuery;
        $this->applyAttributeStructure = $applyAttributeStructure;
        $this->qualityHighlightsProvider = $qualityHighlightsProvider;
        $this->pendingItemsRepository = $pendingItemsRepository;
    }

    public function synchronize(Lock $lock, int $batchSize): void
    {
        $this->synchronizeUpdatedAttributes($lock, $batchSize);
        $this->synchronizeDeletedAttributes($lock, $batchSize);
    }

    private function synchronizeUpdatedAttributes(Lock $lock, int $batchSize): void
    {
        do {
            $attributeCodes = $this->pendingItemIdentifiersQuery->getUpdatedAttributeCodes($lock, $batchSize);
            if (! empty($attributeCodes)) {
                $this->applyAttributeStructure->apply(array_values($attributeCodes));

                $this->pendingItemsRepository->removeUpdatedAttributes($attributeCodes, $lock);
            }
        } while (count($attributeCodes) >= $batchSize);
    }

    private function synchronizeDeletedAttributes(Lock $lock, int $batchSize): void
    {
        do {
            $attributeCodes = $this->pendingItemIdentifiersQuery->getDeletedAttributeCodes($lock, $batchSize);
            if (! empty($attributeCodes)) {
                foreach ($attributeCodes as $attributeCode) {
                    $this->qualityHighlightsProvider->deleteAttribute($attributeCode);
                }

                $this->pendingItemsRepository->removeDeletedAttributes($attributeCodes, $lock);
            }
        } while (count($attributeCodes) >= $batchSize);
    }
}
