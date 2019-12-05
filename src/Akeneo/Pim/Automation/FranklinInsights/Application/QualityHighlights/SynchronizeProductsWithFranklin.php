<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\QualityHighlightsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\Normalizer\ProductNormalizerInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Write\AsyncRequest;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectPendingItemIdentifiersQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectProductsToApplyQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;

class SynchronizeProductsWithFranklin
{
    /** @var SelectPendingItemIdentifiersQueryInterface */
    private $pendingItemIdentifiersQuery;

    /** @var QualityHighlightsProviderInterface */
    private $qualityHighlightsProvider;

    /** @var PendingItemsRepositoryInterface */
    private $pendingItemsRepository;

    /** @var SelectProductsToApplyQueryInterface */
    private $selectProductsToApplyQuery;

    /** @var ProductNormalizerInterface */
    private $productNormalizer;

    public function __construct(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository,
        SelectProductsToApplyQueryInterface $selectProductsToApplyQuery,
        ProductNormalizerInterface $productNormalizer
    ) {
        $this->pendingItemIdentifiersQuery = $pendingItemIdentifiersQuery;
        $this->qualityHighlightsProvider = $qualityHighlightsProvider;
        $this->pendingItemsRepository = $pendingItemsRepository;
        $this->selectProductsToApplyQuery = $selectProductsToApplyQuery;
        $this->productNormalizer = $productNormalizer;
    }

    public function synchronizeUpdatedProducts(Lock $lock, BatchSize $productsPerRequest, BatchSize $requestsPerPool): void
    {
        $poolSize = $productsPerRequest->toInt() * $requestsPerPool->toInt();

        do {
            $updatedProductIds = $this->pendingItemIdentifiersQuery->getUpdatedProductIds($lock, $poolSize);

            if (empty($updatedProductIds)) {
                continue;
            }

            $chunkedProductIds = array_chunk($updatedProductIds, $productsPerRequest->toInt());
            $asyncRequests = [];

            foreach ($chunkedProductIds as $productIds) {
                $products = array_map(function ($product) {
                    return $this->productNormalizer->normalize($product);
                }, $this->selectProductsToApplyQuery->execute($productIds));

                $asyncRequests[] = new AsyncRequest(
                    $products,
                    function () use ($productIds, $lock) {
                        $this->pendingItemsRepository->removeUpdatedProducts($productIds, $lock);
                    },
                    function () use ($productIds, $lock) {
                        //Remove the lock, we will process those products next time
                        $this->pendingItemsRepository->releaseUpdatedProductsLock($productIds, $lock);
                    }
                );
            }

            $this->qualityHighlightsProvider->applyAsyncProducts($asyncRequests);
        } while (count($updatedProductIds) >= $poolSize);
    }

    public function synchronizeDeletedProducts(Lock $lock, BatchSize $batchSize): void
    {
        do {
            $productIds = $this->pendingItemIdentifiersQuery->getDeletedProductIds($lock, $batchSize->toInt());
            if (! empty($productIds)) {
                try {
                    foreach ($productIds as $productId) {
                        $this->qualityHighlightsProvider->deleteProduct($productId);
                    }
                } catch (BadRequestException $exception) {
                    //The error is logged by the api client
                } catch (\Exception $exception) {
                    //Remove the lock, we will process those entities next time
                    $this->pendingItemsRepository->releaseDeletedProductsLock($productIds, $lock);
                    continue;
                }

                $this->pendingItemsRepository->removeDeletedProducts($productIds, $lock);
            }
        } while (count($productIds) >= $batchSize->toInt());
    }
}
