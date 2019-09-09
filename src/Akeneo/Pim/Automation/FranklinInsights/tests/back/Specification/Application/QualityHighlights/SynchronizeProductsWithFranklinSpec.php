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
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectPendingItemIdentifiersQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectProductsToApplyQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use PhpSpec\ObjectBehavior;

class SynchronizeProductsWithFranklinSpec extends ObjectBehavior
{
    function it_synchronize_products_with_franklin(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository,
        SelectProductsToApplyQueryInterface $selectProductsToApplyQuery,
        ProductNormalizerInterface $productNormalizer
    ) {
        $this->beConstructedWith($pendingItemIdentifiersQuery, $qualityHighlightsProvider, $pendingItemsRepository, $selectProductsToApplyQuery, $productNormalizer);

        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');

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

        $productsIds = [42, 123];
        $pendingItemIdentifiersQuery->getUpdatedProductIds($lock, 100)->willReturn($productsIds);
        $selectProductsToApplyQuery->execute($productsIds)->willReturn([$product1, $product2,]);
        $productNormalizer->normalize($product1)->willReturn($normalizedProduct1);
        $productNormalizer->normalize($product2)->willReturn($normalizedProduct2);
        $qualityHighlightsProvider->applyProducts([$normalizedProduct1, $normalizedProduct2])->shouldBeCalled();
        $pendingItemsRepository->removeUpdatedProducts($productsIds, $lock)->shouldBeCalled();

        $pendingItemIdentifiersQuery->getDeletedProductIds($lock, 100)->willReturn([43, 321]);
        $qualityHighlightsProvider->deleteProduct(43)->shouldBeCalled();
        $qualityHighlightsProvider->deleteProduct(321)->shouldBeCalled();
        $pendingItemsRepository->removeDeletedProducts([43, 321], $lock)->shouldBeCalled();

        $this->synchronize($lock, 100);
    }

    function it_releases_the_lock_on_exception(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository,
        SelectProductsToApplyQueryInterface $selectProductsToApplyQuery,
        ProductNormalizerInterface $productNormalizer
    ) {
        $this->beConstructedWith($pendingItemIdentifiersQuery, $qualityHighlightsProvider, $pendingItemsRepository, $selectProductsToApplyQuery, $productNormalizer);

        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');

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
            'attributes' => []
        ];

        $productsIds = [42];
        $pendingItemIdentifiersQuery->getUpdatedProductIds($lock, 100)->willReturn($productsIds);
        $selectProductsToApplyQuery->execute($productsIds)->willReturn([$product1]);
        $productNormalizer->normalize($product1)->willReturn($normalizedProduct1);
        $qualityHighlightsProvider->applyProducts([$normalizedProduct1])->willThrow(new \Exception());
        $pendingItemsRepository->releaseUpdatedProductsLock($productsIds, $lock)->shouldBeCalled();
        $pendingItemsRepository->removeUpdatedProducts($productsIds, $lock)->shouldNotBeCalled();

        $pendingItemIdentifiersQuery->getDeletedProductIds($lock, 100)->willReturn([43]);
        $qualityHighlightsProvider->deleteProduct(43)->willThrow(new \Exception());
        $pendingItemsRepository->releaseDeletedProductsLock([43], $lock)->shouldBeCalled();
        $pendingItemsRepository->removeDeletedProducts([43], $lock)->shouldNotBeCalled();

        $this->synchronize($lock, 100);
    }

    function it_ignores_bad_request_exception(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository,
        SelectProductsToApplyQueryInterface $selectProductsToApplyQuery,
        ProductNormalizerInterface $productNormalizer
    ) {
        $this->beConstructedWith($pendingItemIdentifiersQuery, $qualityHighlightsProvider, $pendingItemsRepository, $selectProductsToApplyQuery, $productNormalizer);

        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');

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
            'attributes' => []
        ];

        $productsIds = [42];
        $pendingItemIdentifiersQuery->getUpdatedProductIds($lock, 100)->willReturn($productsIds);
        $selectProductsToApplyQuery->execute($productsIds)->willReturn([$product1]);
        $productNormalizer->normalize($product1)->willReturn($normalizedProduct1);
        $qualityHighlightsProvider->applyProducts([$normalizedProduct1])->willThrow(new BadRequestException());
        $pendingItemsRepository->releaseUpdatedProductsLock($productsIds, $lock)->shouldNotBeCalled();
        $pendingItemsRepository->removeUpdatedProducts($productsIds, $lock)->shouldBeCalled();

        $pendingItemIdentifiersQuery->getDeletedProductIds($lock, 100)->willReturn([43]);
        $qualityHighlightsProvider->deleteProduct(43)->willThrow(new BadRequestException());
        $pendingItemsRepository->releaseDeletedProductsLock([43], $lock)->shouldNotBeCalled();
        $pendingItemsRepository->removeDeletedProducts([43], $lock)->shouldBeCalled();

        $this->synchronize($lock, 100);
    }
}
