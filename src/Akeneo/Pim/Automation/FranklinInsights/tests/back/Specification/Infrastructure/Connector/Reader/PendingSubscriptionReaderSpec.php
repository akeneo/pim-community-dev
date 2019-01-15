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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Reader;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Reader\PendingSubscriptionReader;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Cursor\PendingSubscriptionCursor;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class PendingSubscriptionReaderSpec extends ObjectBehavior
{
    public function let(
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        StepExecution $stepExecution
    ): void {
        $subscriptionCursor = new PendingSubscriptionCursor($subscriptionRepository->getWrappedObject(), 2);

        $this->beConstructedWith($subscriptionCursor);
        $this->setStepExecution($stepExecution);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(PendingSubscriptionReader::class);
    }

    public function it_is_a_reader(): void
    {
        $this->shouldImplement(ItemReaderInterface::class);
    }

    public function it_is_step_execution_aware(): void
    {
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    public function it_iterates_on_the_pending_subscriptions($subscriptionRepository): void
    {
        $subscription1 = new ProductSubscription(42, 'subscription-42', ['asin' => 'asin-42']);
        $subscription2 = new ProductSubscription(43, 'subscription-43', ['upc' => 'upc-43']);
        $subscription3 = new ProductSubscription(44, 'subscription-44', ['asin' => 'asin-44']);

        $subscriptionRepository->findPendingSubscriptions(2, null)->willReturn([$subscription1, $subscription2]);
        $subscriptionRepository->findPendingSubscriptions(2, 'subscription-43')->willReturn([$subscription3]);
        $subscriptionRepository->findPendingSubscriptions(2, 'subscription-44')->willReturn([]);

        $this->read()->shouldReturn($subscription1);
        $this->read()->shouldReturn($subscription2);
        $this->read()->shouldReturn($subscription3);
        $this->read()->shouldReturn(null);
    }

    public function it_increments_the_reading_count($subscriptionRepository, $stepExecution): void
    {
        $subscription1 = new ProductSubscription(42, 'subscription-42', ['asin' => 'asin-42']);
        $subscription2 = new ProductSubscription(43, 'subscription-43', ['upc' => 'upc-43']);

        $subscriptionRepository->findPendingSubscriptions(2, null)->willReturn([$subscription1, $subscription2]);
        $subscriptionRepository->findPendingSubscriptions(2, 'subscription-43')->willReturn([]);

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(2);

        $this->read()->shouldReturn($subscription1);
        $this->read()->shouldReturn($subscription2);
        $this->read()->shouldReturn(null);
    }
}
