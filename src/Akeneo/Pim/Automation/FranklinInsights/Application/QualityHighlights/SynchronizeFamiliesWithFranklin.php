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
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;

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

    public function synchronize(Lock $lock, int $batchSize): void
    {
        $this->synchronizeUpdatedFamilies($lock, $batchSize);
        $this->synchronizeDeletedFamilies($lock, $batchSize);
    }

    private function synchronizeUpdatedFamilies(Lock $lock, int $batchSize): void
    {
        do {
            $familyCodes = $this->pendingItemIdentifiersQuery->getUpdatedFamilyCodes($lock, $batchSize);
            if (! empty($familyCodes)) {
                try {
                    $this->qualityHighlightsProvider->applyFamilies(array_values($familyCodes));
                } catch (FranklinServerException | InvalidTokenException $exception) {
                    //Remove the lock, we will process those entities next time
                    $this->pendingItemsRepository->releaseUpdatedFamiliesLock($familyCodes, $lock);
                    continue;
                } catch (BadRequestException $exception) {
                    //The error is logged by the api client
                }

                $this->pendingItemsRepository->removeUpdatedFamilies($familyCodes, $lock);
            }
        } while (count($familyCodes) >= $batchSize);
    }

    private function synchronizeDeletedFamilies(Lock $lock, int $batchSize)
    {
        do {
            $familyCodes = $this->pendingItemIdentifiersQuery->getDeletedFamilyCodes($lock, $batchSize);
            if (! empty($familyCodes)) {
                try {
                    foreach ($familyCodes as $familyCode) {
                        $this->qualityHighlightsProvider->deleteFamily($familyCode);
                    }
                } catch (FranklinServerException | InvalidTokenException $exception) {
                    //Remove the lock, we will process those entities next time
                    $this->pendingItemsRepository->releaseDeletedFamiliesLock($familyCodes, $lock);
                    continue;
                } catch (BadRequestException $exception) {
                    //The error is logged by the api client
                }

                $this->pendingItemsRepository->removeDeletedFamilies($familyCodes, $lock);
            }
        } while (count($familyCodes) >= $batchSize);
    }
}
