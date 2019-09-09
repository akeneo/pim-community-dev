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
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectPendingItemIdentifiersQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectProductsToApplyQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;

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

    public function synchronize(Lock $lock, int $batchSize): void
    {
        $this->synchronizeUpdatedProducts($lock, $batchSize);
        $this->synchronizeDeletedProducts($lock, $batchSize);
    }

    private function synchronizeUpdatedProducts(Lock $lock, int $batchSize): void
    {
        do {
            $productsIds = $this->pendingItemIdentifiersQuery->getUpdatedProductIds($lock, $batchSize);
            if (! empty($productsIds)) {
                $products = array_map(function ($product) {
                    return $this->productNormalizer->normalize($product);
                }, $this->selectProductsToApplyQuery->execute($productsIds));

                $this->qualityHighlightsProvider->applyProducts($products);

                $this->pendingItemsRepository->removeUpdatedProducts($productsIds, $lock);
            }
        } while (count($productsIds) >= $batchSize);
    }

    private function synchronizeDeletedProducts(Lock $lock, int $batchSize): void
    {
        do {
            $productIds = $this->pendingItemIdentifiersQuery->getDeletedProductIds($lock, $batchSize);
            if (! empty($productIds)) {
                foreach ($productIds as $productId) {
                    $this->qualityHighlightsProvider->deleteProduct($productId);
                }
                $this->pendingItemsRepository->removeDeletedProducts($productIds, $lock);
            }
        } while (count($productIds) >= $batchSize);
    }
}
