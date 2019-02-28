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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Processor;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SuggestedDataProcessorSpec extends ObjectBehavior
{
    public function let(ProductSubscriptionRepositoryInterface $subscriptionRepository): void
    {
        $this->beConstructedWith($subscriptionRepository);
    }

    public function it_is_a_processor(): void
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    public function it_is_step_execution_aware(): void
    {
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    public function it_throws_an_invalid_item_exception_when_subscription_does_not_exist_anymore(
        $subscriptionRepository
    ): void {
        $subscriptionResponse = new ProductSubscriptionResponse(42, new SubscriptionId('fake-subscription-id'), [], true, false);
        $subscriptionRepository->findOneByProductId(42)->willReturn(null);

        $this->shouldThrow(InvalidItemException::class)->during('process', [$subscriptionResponse]);
    }

    public function it_returns_a_susbcription($subscriptionRepository): void
    {
        $subscriptionResponse = new ProductSubscriptionResponse(42, new SubscriptionId('fake-subscription-id'), [], true, false);

        $subscription = new ProductSubscription(42, new SubscriptionId('fake-subscription-id'), []);
        $subscriptionRepository->findOneByProductId(42)->willReturn($subscription);

        $this->process($subscriptionResponse)->shouldReturn($subscription);
        Assert::false($subscription->isCancelled());
    }

    public function it_returns_a_cancelled_subscription($subscriptionRepository): void
    {
        $subscriptionResponse = new ProductSubscriptionResponse(42, new SubscriptionId('fake-subscription-id'), [], true, true);

        $subscription = new ProductSubscription(42, new SubscriptionId('fake-subscription-id'), []);
        $subscriptionRepository->findOneByProductId(42)->willReturn($subscription);

        Assert::false($subscription->isCancelled());
        $this->process($subscriptionResponse)->shouldReturn($subscription);
        Assert::true($subscription->isCancelled());
    }
}
