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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\QualityHighlightsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\Normalizer\ProductNormalizerInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Read\Product;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Write\AsyncRequest;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectPendingItemIdentifiersQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectProductsToApplyQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SynchronizeProductsWithFranklinSpec extends ObjectBehavior
{
    function let(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository,
        SelectProductsToApplyQueryInterface $selectProductsToApplyQuery,
        ProductNormalizerInterface $productNormalizer
    ) {
        $this->beConstructedWith($pendingItemIdentifiersQuery, $qualityHighlightsProvider, $pendingItemsRepository, $selectProductsToApplyQuery, $productNormalizer);
    }

    function it_synchronizes_updated_products_with_franklin(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        SelectProductsToApplyQueryInterface $selectProductsToApplyQuery,
        ProductNormalizerInterface $productNormalizer
    ) {
        $product1 = new Product(
            new ProductId(42),
            new FamilyCode('mugs'),
            [
                'name' => [
                    'ecommerce' => [
                        'en_US' => 'Ziggy'
                    ]
                ]
            ]
        );
        $normalizedProduct1 = [
            'catalog_product_id' => 42,
            'family' => 'mugs',
            'attributes' => [
                'name' => [
                    [
                        'value' => 'Ziggy',
                        'locale' => 'en_US',
                        'channel' => 'ecommerce',
                    ]
                ]
            ]
        ];

        $product2 = new Product(
            new ProductId(123),
            new FamilyCode('projectors'),
            [
                'brand' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'BenQ'
                    ]
                ]
            ]
        );
        $normalizedProduct2 = [
            'catalog_product_id' => 123,
            'family' => 'projectors',
            'attributes' => [
                'brand' => [
                    [
                        'value' => 'BenQ',
                        'locale' => null,
                        'channel' => null,
                    ]
                ]
            ]
        ];

        $product3 = new Product(
            new ProductId(333),
            new FamilyCode('mugs'),
            [
                'name' => [
                    'ecommerce' => [
                        'en_US' => 'Another Ziggy mug'
                    ]
                ]
            ]
        );
        $normalizedProduct3 = [
            'catalog_product_id' => 333,
            'family' => 'mugs',
            'attributes' => [
                'name' => [
                    [
                        'value' => 'Another Ziggy mug',
                        'locale' => 'en_US',
                        'channel' => 'ecommerce',
                    ]
                ]
            ]
        ];

        $productNormalizer->normalize($product1)->willReturn($normalizedProduct1);
        $productNormalizer->normalize($product2)->willReturn($normalizedProduct2);
        $productNormalizer->normalize($product3)->willReturn($normalizedProduct3);

        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');
        $productsPerRequest = new BatchSize(2);
        $requestsPerPool = new BatchSize(3);

        $pendingItemIdentifiersQuery->getUpdatedProductIds($lock, 6)->willReturn([42, 123, 333]);
        $selectProductsToApplyQuery->execute([42, 123])->willReturn([$product1, $product2]);
        $selectProductsToApplyQuery->execute([333])->willReturn([$product3]);
        $qualityHighlightsProvider->applyAsyncProducts(Argument::that(
            function ($asyncRequests) use ($normalizedProduct1, $normalizedProduct2, $normalizedProduct3) {
                if (!is_array($asyncRequests) || count(($asyncRequests)) !== 2) {
                    return false;
                }
                $asyncRequest1 = $asyncRequests[0];
                $asyncRequest2 = $asyncRequests[1];
                if (!$asyncRequest1 instanceof AsyncRequest || !$asyncRequest2 instanceof AsyncRequest) {
                    return false;
                }
                if ($asyncRequest1->getData() != [$normalizedProduct1, $normalizedProduct2]) {
                    return false;
                }
                if ($asyncRequest2->getData() != [$normalizedProduct3]) {
                    return false;
                }
                return true;
        }))->shouldBeCalled();

        $this->synchronizeUpdatedProducts($lock, $productsPerRequest, $requestsPerPool);
    }

    public function it_does_nothing_if_there_is_no_updated_products(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider
    ) {
        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');
        $productsPerRequest = new BatchSize(3);
        $requestsPerPool = new BatchSize(2);

        $pendingItemIdentifiersQuery->getUpdatedProductIds($lock, 6)->willReturn([]);
        $qualityHighlightsProvider->applyAsyncProducts(Argument::any())->shouldNotBeCalled();

        $this->synchronizeUpdatedProducts($lock, $productsPerRequest, $requestsPerPool);
    }

    function it_synchronize_deleted_products_with_franklin(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository
    ) {
        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');

        $pendingItemIdentifiersQuery->getDeletedProductIds($lock, 100)->willReturn([43, 321]);
        $qualityHighlightsProvider->deleteProduct(43)->shouldBeCalled();
        $qualityHighlightsProvider->deleteProduct(321)->shouldBeCalled();
        $pendingItemsRepository->removeDeletedProducts([43, 321], $lock)->shouldBeCalled();

        $this->synchronizeDeletedProducts($lock, new BatchSize(100));
    }

    function it_releases_the_lock_on_exception_when_synchronizing_deleted_products(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository
    ) {
        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');

        $pendingItemIdentifiersQuery->getDeletedProductIds($lock, 100)->willReturn([43]);
        $qualityHighlightsProvider->deleteProduct(43)->willThrow(new \Exception());
        $pendingItemsRepository->releaseDeletedProductsLock([43], $lock)->shouldBeCalled();
        $pendingItemsRepository->removeDeletedProducts([43], $lock)->shouldNotBeCalled();

        $this->synchronizeDeletedProducts($lock, new BatchSize(100));
    }

    function it_ignores_bad_request_exception_when_synchronizing_deleted_products(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository
    ) {
        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');

        $pendingItemIdentifiersQuery->getDeletedProductIds($lock, 100)->willReturn([43]);
        $qualityHighlightsProvider->deleteProduct(43)->willThrow(new BadRequestException());
        $pendingItemsRepository->releaseDeletedProductsLock([43], $lock)->shouldNotBeCalled();
        $pendingItemsRepository->removeDeletedProducts([43], $lock)->shouldBeCalled();

        $this->synchronizeDeletedProducts($lock, new BatchSize(100));
    }
}
