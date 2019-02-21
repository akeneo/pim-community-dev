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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Indexing\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\ProductSubscriptionsExistQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Indexing\Normalizer\ProductSubscriptionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class ProductSubscriptionNormalizerSpec extends ObjectBehavior
{
    public function let(ProductSubscriptionsExistQueryInterface $isProductSubscribedToFranklinQuery): void
    {
        $this->beConstructedWith($isProductSubscribedToFranklinQuery);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProductSubscriptionNormalizer::class);
    }

    public function it_support_products_and_variant_products(ProductInterface $product): void
    {
        $this->supportsNormalization($product, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($product, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);

        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
    }

    public function it_normalizes_product_subscription(ProductInterface $product, $isProductSubscribedToFranklinQuery): void
    {
        $product->getId()->willReturn(42);

        $isProductSubscribedToFranklinQuery->execute([42])->willReturn([42 => false]);
        $this->normalize($product)->shouldReturn(['franklin_subscription' => false]);

        $isProductSubscribedToFranklinQuery->execute([42])->willReturn([42 => true]);
        $this->normalize($product)->shouldReturn(['franklin_subscription' => true]);
    }
}
