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
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectUpdatedProductsIdsToApplyQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;

class SynchronizeProductsWithFranklin
{
    /** @var int Requests maximum size (Octets) */
    const REQUEST_MAX_SIZE = 8000000;

    /** @var int Number of products removed at each attempt to reduce the size of an over sized request */
    const REQUEST_REDUCTION_SIZE = 10;

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

    /** @var SelectUpdatedProductsIdsToApplyQueryInterface */
    private $selectUpdatedProductsIdsToApplyQuery;

    public function __construct(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        SelectUpdatedProductsIdsToApplyQueryInterface $selectUpdatedProductsIdsToApplyQuery,
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
        $this->selectUpdatedProductsIdsToApplyQuery = $selectUpdatedProductsIdsToApplyQuery;
    }

    public function synchronizeUpdatedProducts(Lock $lock, BatchSize $productsPerRequest, BatchSize $requestsPerPool): void
    {
        $poolSize = new BatchSize($productsPerRequest->toInt() * $requestsPerPool->toInt());

        do {
            $updatedProductIds = $this->selectUpdatedProductsIdsToApplyQuery->execute($lock, $poolSize);

            if (empty($updatedProductIds)) {
                continue;
            }

            $chunkedProductIds = array_chunk($updatedProductIds, $productsPerRequest->toInt());
            $asyncRequests = [];

            foreach ($chunkedProductIds as $productIds) {
                $asyncRequests[] = $this->buildSizeLimitedAsyncRequest($lock, $productIds);
            }

            $this->qualityHighlightsProvider->applyAsyncProducts($asyncRequests);
        } while (count($updatedProductIds) >= $poolSize->toInt());
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

    private function buildSizeLimitedAsyncRequest(Lock $lock, array $productIds): AsyncRequest
    {
        $products = $this->selectProductsToApplyQuery->execute($productIds);
        $normalizedProducts = [];
        foreach ($products as $product) {
            $normalizedProducts[$product->getId()->toInt()] = $this->productNormalizer->normalize($product);
        }

        $productsCount = count($normalizedProducts);
        /*
         * We use REQUEST_REDUCTION_SIZE as minimum size to avoid to have an empty request that would cause an infinite loop (products never unlocked).
         * TODO: use mb_strlen when available to handle multibyte characters
         */
        while ($productsCount > self::REQUEST_REDUCTION_SIZE && strlen(json_encode($normalizedProducts)) >= self::REQUEST_MAX_SIZE) {
            $productsCount -= self::REQUEST_REDUCTION_SIZE;
            $normalizedProducts = array_splice($normalizedProducts, 0, $productsCount);
        }

        $productIds = array_keys($normalizedProducts);

        return new AsyncRequest(
            array_values($normalizedProducts),
            function () use ($productIds, $lock) {
                $this->pendingItemsRepository->removeUpdatedProducts($productIds, $lock);
            },
            function () use ($productIds, $lock) {
                //Remove the lock, we will process those products next time
                $this->pendingItemsRepository->releaseUpdatedProductsLock($productIds, $lock);
            }
        );
    }
}
