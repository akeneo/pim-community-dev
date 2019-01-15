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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Cursor;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Cursor\PendingSubscriptionCursor;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class PendingSubscriptionCursorSpec extends ObjectBehavior
{
    public function let(ProductSubscriptionRepositoryInterface $subscriptionRepository): void
    {
        $this->beConstructedWith($subscriptionRepository, 2);
    }

    public function it_should_have_type(): void
    {
        $this->shouldHaveType(PendingSubscriptionCursor::class);
    }

    public function it_is_an_iterator(): void
    {
        $this->shouldImplement(\Iterator::class);
    }

    public function it_returns_the_current_element($subscriptionRepository): void
    {
        $subscription1 = new ProductSubscription(42, 'subscription-42', ['asin' => 'asin-42']);
        $subscription2 = new ProductSubscription(43, 'subscription-43', ['upc' => 'upc-43']);

        $subscriptionRepository->findPendingSubscriptions(2, null)->willReturn([$subscription1, $subscription2]);

        $this->current()->shouldReturn($subscription1);
    }

    public function it_moves_to_the_next_element($subscriptionRepository): void
    {
        $subscription1 = new ProductSubscription(42, 'subscription-42', ['asin' => 'asin-42']);
        $subscription2 = new ProductSubscription(43, 'subscription-43', ['upc' => 'upc-43']);

        $subscriptionRepository->findPendingSubscriptions(2, null)->willReturn([$subscription1, $subscription2]);

        $this->current()->shouldReturn($subscription1);
        $this->next()->shouldReturn(null);
        $this->current()->shouldReturn($subscription2);
    }

    public function it_moves_to_the_first_element_on_first_load($subscriptionRepository): void
    {
        $subscription1 = new ProductSubscription(42, 'subscription-42', ['asin' => 'asin-42']);
        $subscription2 = new ProductSubscription(43, 'subscription-43', ['upc' => 'upc-43']);

        $subscriptionRepository->findPendingSubscriptions(2, null)->willReturn([$subscription1, $subscription2]);

        $this->next()->shouldReturn(null);
        $this->current()->shouldReturn($subscription1);
    }

    public function it_moves_to_the_next_page_automatically($subscriptionRepository): void
    {
        $subscription1 = new ProductSubscription(42, 'subscription-42', ['asin' => 'asin-42']);
        $subscription2 = new ProductSubscription(43, 'subscription-43', ['upc' => 'upc-43']);
        $subscription3 = new ProductSubscription(44, 'subscription-44', ['asin' => 'asin-44']);

        $subscriptionRepository->findPendingSubscriptions(2, null)->willReturn([$subscription1, $subscription2]);
        $subscriptionRepository->findPendingSubscriptions(2, 'subscription-43')->willReturn([$subscription3]);

        $this->current()->shouldReturn($subscription1);
        $this->next()->shouldReturn(null);
        $this->current()->shouldReturn($subscription2);
        $this->next()->shouldReturn(null);
        $this->current()->shouldReturn($subscription3);
    }

    public function it_returns_the_current_index($subscriptionRepository): void
    {
        $subscription1 = new ProductSubscription(42, 'subscription-42', ['asin' => 'asin-42']);
        $subscription2 = new ProductSubscription(43, 'subscription-43', ['upc' => 'upc-43']);

        $subscriptionRepository->findPendingSubscriptions(2, null)->willReturn([$subscription1, $subscription2]);

        $this->key()->shouldReturn(0);
        $this->next();
        $this->key()->shouldReturn(1);
    }

    public function it_validates_the_current_position($subscriptionRepository): void
    {
        $subscription1 = new ProductSubscription(42, 'subscription-42', ['asin' => 'asin-42']);
        $subscription2 = new ProductSubscription(43, 'subscription-43', ['upc' => 'upc-43']);

        $subscriptionRepository->findPendingSubscriptions(2, null)->willReturn([$subscription1, $subscription2]);
        $subscriptionRepository->findPendingSubscriptions(2, 'subscription-43')->willReturn([]);

        $this->valid()->shouldReturn(false);

        $this->next();
        $this->valid()->shouldReturn(true);
        $this->next();
        $this->valid()->shouldReturn(true);
        $this->next();
        $this->valid()->shouldReturn(false);
    }

    public function it_rewinds_to_the_first_element($subscriptionRepository): void
    {
        $subscription1 = new ProductSubscription(42, 'subscription-42', ['asin' => 'asin-42']);
        $subscription2 = new ProductSubscription(43, 'subscription-43', ['upc' => 'upc-43']);

        $subscriptionRepository->findPendingSubscriptions(2, null)->willReturn([$subscription1, $subscription2]);

        $this->rewind()->shouldReturn(null);
        $this->next();
        $this->current()->shouldReturn($subscription2);
        $this->rewind()->shouldReturn(null);
        $this->current()->shouldReturn($subscription1);
    }
}
