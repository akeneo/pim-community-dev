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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Memory;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Memory\InMemoryProductSubscriptionRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class InMemoryProductSubscriptionRepositorySpec extends ObjectBehavior
{
    public function it_is_a_product_subscription_repository(): void
    {
        $this->shouldImplement(ProductSubscriptionRepositoryInterface::class);
    }

    public function it_is_the_in_memory_implementation_of_the_product_subscription_repository(): void
    {
        $this->shouldBeAnInstanceOf(InMemoryProductSubscriptionRepository::class);
    }

    public function it_saves_a_product_subscription(): void
    {
        $product = new Product();
        $product->setId(42);
        $subscription = new ProductSubscription($product, 'a-fake-subscription', ['sku' => '72527273070']);
        $this->save($subscription);

        $this->findOneByProductId(42)->shouldReturn($subscription);
    }

    public function it_returns_null_if_you_asked_for_a_product_without_subscription(): void
    {
        $this->findOneByProductId(42)->shouldReturn(null);
    }

    public function it_finds_product_subscriptions_with_suggested_data(): void
    {
        $product = new Product();
        $product->setId(42);
        $subscription = new ProductSubscription($product, 'a-fake-subscription', ['sku' => '72527273070']);
        $subscription->setSuggestedData(new SuggestedData(['foo' => 'bar']));
        $this->save($subscription);

        $otherProduct = new Product();
        $otherProduct->setId(44);
        $otherSubscription = new ProductSubscription(
            $otherProduct,
            'another-fake-subscription',
            ['sku' => '72527273070']
        );
        $this->save($otherSubscription);

        $this->findPendingSubscriptions()->shouldReturn([$subscription]);
    }

    public function it_deletes_a_product_susbcription(
        ProductSubscription $subscription,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(42);
        $subscription->getProduct()->willReturn($product);

        $this->save($subscription);
        $this->findOneByProductId(42)->shouldReturn($subscription);

        $this->delete($subscription);
        $this->findOneByProductId(42)->shouldReturn(null);
    }
}
