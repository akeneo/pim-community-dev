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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Memory;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Memory\InMemoryProductSubscriptionRepository;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class InMemoryProductSubscriptionRepositorySpec extends ObjectBehavior
{
    public function let(
        FamilyRepositoryInterface $familyRepository,
        ProductRepositoryInterface $productRepository
    ): void {
        $this->beConstructedWith($familyRepository, $productRepository);
    }

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
        $subscription = new ProductSubscription(42, 'a-fake-subscription', ['sku' => '72527273070']);
        $this->save($subscription);

        $this->findOneByProductId(42)->shouldReturn($subscription);
    }

    public function it_bulk_saves_subscriptions(): void
    {
        $subscription = new ProductSubscription(42, 'a-fake-subscription', ['sku' => '72527273070']);
        $subscription2 = new ProductSubscription(43, 'fake-id-43', ['asin' => '123']);
        $this->bulkSave([$subscription, $subscription2]);

        $this->findOneByProductId(42)->shouldReturn($subscription);
        $this->findOneByProductId(43)->shouldReturn($subscription2);
    }

    public function it_returns_null_if_you_asked_for_a_product_without_subscription(): void
    {
        $this->findOneByProductId(42)->shouldReturn(null);
    }

    public function it_finds_product_subscriptions_with_suggested_data(): void
    {
        $subscription = new ProductSubscription(42, 'a-fake-subscription', ['sku' => '72527273070']);
        $subscription->setSuggestedData(new SuggestedData([['pimAttributeCode' => 'foo', 'value' => 'bar']]));
        $this->save($subscription);

        $otherSubscription = new ProductSubscription(
            44,
            'another-fake-subscription',
            ['sku' => '72527273070']
        );
        $this->save($otherSubscription);

        $this->findPendingSubscriptions(10, null)->shouldReturn([$subscription]);
    }

    public function it_searches_pending_subscriptions(): void
    {
        $subscription1 = new ProductSubscription(42, 'fake-id', ['asin' => 'ABC']);
        $subscription1->setSuggestedData(new SuggestedData([['pimAttributeCode' => 'foo', 'value' => 'bar']]));
        $this->save($subscription1);
        $subscription2 = new ProductSubscription(44, 'abc', ['asin' => 'ABC']);
        $subscription2->setSuggestedData(new SuggestedData([['pimAttributeCode' => 'foo', 'value' => 'bar']]));
        $this->save($subscription2);
        $subscription3 = new ProductSubscription(56, 'def', ['asin' => 'ABC']);
        $subscription3->setSuggestedData(new SuggestedData([['pimAttributeCode' => 'foo', 'value' => 'bar']]));
        $this->save($subscription3);

        $this->findPendingSubscriptions(10, null)->shouldReturn([$subscription2, $subscription3, $subscription1]);
        $this->findPendingSubscriptions(10, 'def')->shouldReturn([$subscription1]);
        $this->findPendingSubscriptions(10, 'abc')->shouldReturn([$subscription3, $subscription1]);
    }

    public function it_deletes_a_product_susbcription(): void
    {
        $subscription = new ProductSubscription(42, 'fake-id', ['asin' => 'ABC']);
        $this->save($subscription);
        $this->findOneByProductId(42)->shouldReturn($subscription);

        $this->delete($subscription);
        $this->findOneByProductId(42)->shouldReturn(null);
    }

    public function it_bulk_deletes_subscriptions(): void
    {
        $subscription = new ProductSubscription(42, 'fake-id-42', ['asin' => 'ABC']);
        $subscription2 = new ProductSubscription(43, 'fake-id-43', ['asin' => '123']);
        $this->bulkSave([$subscription, $subscription2]);

        $this->findOneByProductId(42)->shouldReturn($subscription);
        $this->findOneByProductId(43)->shouldReturn($subscription2);

        $this->bulkDelete([$subscription, $subscription2]);
        $this->findOneByProductId(42)->shouldReturn(null);
        $this->findOneByProductId(43)->shouldReturn(null);
    }

    public function it_empties_suggested_data_for_specified_product_ids(): void
    {
        $subscription = new ProductSubscription(42, 'fake-subscription-id', []);
        $subscription->setSuggestedData(new SuggestedData([['pimAttributeCode' => 'foo', 'value' => 'bar']]));

        $this->save($subscription);
        Assert::false($subscription->getSuggestedData()->isEmpty());

        $this->emptySuggestedDataByProducts([42]);
        Assert::true($subscription->getSuggestedData()->isEmpty());
    }

    public function it_empties_suggested_data(): void
    {
        $subscription1 = new ProductSubscription(42, 'fake-id', []);
        $subscription1->setSuggestedData(new SuggestedData([['pimAttributeCode' => 'foo', 'value' => 'bar']]));
        $subscription2 = new ProductSubscription(43, 'other-fake-id', []);
        $subscription2->setSuggestedData(new SuggestedData([['pimAttributeCode' => 'bar', 'value' => 'baz']]));
        Assert::false($subscription1->getSuggestedData()->isEmpty());
        Assert::false($subscription2->getSuggestedData()->isEmpty());

        $this->save($subscription1);
        $this->save($subscription2);

        $this->emptySuggestedData();
        Assert::true($subscription1->getSuggestedData()->isEmpty());
        Assert::true($subscription2->getSuggestedData()->isEmpty());
    }
}
