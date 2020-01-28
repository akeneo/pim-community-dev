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
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Write\AsyncRequest;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectAttributesToApplyQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectPendingItemIdentifiersQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Psr\Log\LoggerInterface;

class SynchronizeAttributesWithFranklin
{
    /** @var SelectPendingItemIdentifiersQueryInterface */
    private $pendingItemIdentifiersQuery;

    /** @var QualityHighlightsProviderInterface */
    private $qualityHighlightsProvider;

    /** @var PendingItemsRepositoryInterface */
    private $pendingItemsRepository;

    /** @var SelectAttributesToApplyQueryInterface */
    private $selectAttributesToApplyQuery;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        SelectAttributesToApplyQueryInterface $selectAttributesToApplyQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository,
        LoggerInterface $logger
    ) {
        $this->pendingItemIdentifiersQuery = $pendingItemIdentifiersQuery;
        $this->qualityHighlightsProvider = $qualityHighlightsProvider;
        $this->pendingItemsRepository = $pendingItemsRepository;
        $this->selectAttributesToApplyQuery = $selectAttributesToApplyQuery;
        $this->logger = $logger;
    }

    public function synchronizeUpdatedAttributes(Lock $lock, BatchSize $attributesPerRequest, BatchSize $requestsPerPool): void
    {
        $poolSize = $attributesPerRequest->toInt() * $requestsPerPool->toInt();

        do {
            $updatedAttributeCodes = $this->pendingItemIdentifiersQuery->getUpdatedAttributeCodes($lock, $poolSize);

            if (empty($updatedAttributeCodes)) {
                continue;
            }

            $chunkedAttributeCodes = array_chunk($updatedAttributeCodes, $attributesPerRequest->toInt());
            $asyncRequests = [];

            foreach ($chunkedAttributeCodes as $attributeCodes) {
                $attributes = $this->selectAttributesToApplyQuery->execute($attributeCodes);
                $asyncRequests[] = new AsyncRequest(
                    $attributes,
                    function () use ($attributeCodes, $lock) {
                        $this->pendingItemsRepository->removeUpdatedAttributes($attributeCodes, $lock);
                    },
                    function ($reason) use ($attributeCodes, $lock) {
                        $this->logger->error($reason->getMessage());
                        $this->pendingItemsRepository->releaseUpdatedAttributesLock($attributeCodes, $lock);
                    }
                );
            }
            $this->qualityHighlightsProvider->applyAsyncAttributeStructure($asyncRequests);
        } while (count($updatedAttributeCodes) >= $poolSize);
    }

    public function synchronizeDeletedAttributes(Lock $lock, BatchSize $batchSize): void
    {
        do {
            $attributeCodes = $this->pendingItemIdentifiersQuery->getDeletedAttributeCodes($lock, $batchSize->toInt());
            if (! empty($attributeCodes)) {
                try {
                    foreach ($attributeCodes as $attributeCode) {
                        $this->qualityHighlightsProvider->deleteAttribute($attributeCode);
                    }
                } catch (\Exception $exception) {
                    //Remove the lock, we will process those entities next time
                    $this->logger->error($exception->getMessage());
                    $this->pendingItemsRepository->releaseDeletedAttributesLock($attributeCodes, $lock);
                    continue;
                }

                $this->pendingItemsRepository->removeDeletedAttributes($attributeCodes, $lock);
            }
        } while (count($attributeCodes) >= $batchSize->toInt());
    }
}
