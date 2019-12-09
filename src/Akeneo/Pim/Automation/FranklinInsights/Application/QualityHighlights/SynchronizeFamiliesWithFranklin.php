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
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectFamiliesToApplyQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectPendingItemIdentifiersQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;

class SynchronizeFamiliesWithFranklin
{
    /** @var SelectPendingItemIdentifiersQueryInterface */
    private $pendingItemIdentifiersQuery;

    /** @var QualityHighlightsProviderInterface */
    private $qualityHighlightsProvider;

    /** @var PendingItemsRepositoryInterface */
    private $pendingItemsRepository;

    /** @var SelectFamiliesToApplyQueryInterface */
    private $selectFamiliesToApplyQuery;

    public function __construct(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository,
        SelectFamiliesToApplyQueryInterface $selectFamiliesToApplyQuery
    ) {
        $this->pendingItemIdentifiersQuery = $pendingItemIdentifiersQuery;
        $this->qualityHighlightsProvider = $qualityHighlightsProvider;
        $this->pendingItemsRepository = $pendingItemsRepository;
        $this->selectFamiliesToApplyQuery = $selectFamiliesToApplyQuery;
    }

    public function synchronize(Lock $lock, BatchSize $batchSize): void
    {
        $this->synchronizeUpdatedFamilies($lock, $batchSize);
        $this->synchronizeDeletedFamilies($lock, $batchSize);
    }

    private function synchronizeUpdatedFamilies(Lock $lock, BatchSize $batchSize): void
    {
        do {
            $familyCodes = $this->pendingItemIdentifiersQuery->getUpdatedFamilyCodes($lock, $batchSize->toInt());
            if (! empty($familyCodes)) {
                try {
                    $families = $this->selectFamiliesToApplyQuery->execute($familyCodes);
                    $this->qualityHighlightsProvider->applyFamilies($families);
                } catch (BadRequestException $exception) {
                    //The error is logged by the api client
                } catch (\Exception $exception) {
                    //Remove the lock, we will process those entities next time
                    $this->pendingItemsRepository->releaseUpdatedFamiliesLock($familyCodes, $lock);
                    continue;
                }

                $this->pendingItemsRepository->removeUpdatedFamilies($familyCodes, $lock);
            }
        } while (count($familyCodes) >= $batchSize->toInt());
    }

    private function synchronizeDeletedFamilies(Lock $lock, BatchSize $batchSize)
    {
        do {
            $familyCodes = $this->pendingItemIdentifiersQuery->getDeletedFamilyCodes($lock, $batchSize->toInt());
            if (! empty($familyCodes)) {
                try {
                    foreach ($familyCodes as $familyCode) {
                        $this->qualityHighlightsProvider->deleteFamily($familyCode);
                    }
                } catch (BadRequestException $exception) {
                    //The error is logged by the api client
                } catch (\Exception $exception) {
                    //Remove the lock, we will process those entities next time
                    $this->pendingItemsRepository->releaseDeletedFamiliesLock($familyCodes, $lock);
                    continue;
                }

                $this->pendingItemsRepository->removeDeletedFamilies($familyCodes, $lock);
            }
        } while (count($familyCodes) >= $batchSize->toInt());
    }
}
