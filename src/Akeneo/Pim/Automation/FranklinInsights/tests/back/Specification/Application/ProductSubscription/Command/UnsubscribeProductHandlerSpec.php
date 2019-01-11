<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use PhpSpec\ObjectBehavior;

class UnsubscribeProductHandlerSpec extends ObjectBehavior
{
    public function let(
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        SubscriptionProviderInterface $subscriptionProvider
    ): void {
        $this->beConstructedWith(
            $subscriptionRepository,
            $subscriptionProvider
        );
    }

    public function it_is_an_unsubscribe_product_handler(): void
    {
        $this->shouldHaveType(UnsubscribeProductHandler::class);
    }

    public function it_throws_an_exception_if_the_product_is_not_subscribed(
        $subscriptionRepository
    ): void {
        $productId = 42;
        $subscriptionRepository->findOneByProductId($productId)->willReturn(null);

        $command = new UnsubscribeProductCommand($productId);
        $this
            ->shouldThrow(ProductNotSubscribedException::class)
            ->during('handle', [$command]);
    }

    public function it_unsubscribes_the_product_and_deletes_the_subscription(
        $subscriptionRepository,
        $subscriptionProvider,
        ProductSubscription $subscription
    ): void {
        $productId = 42;
        $subscriptionId = 'foo-bar';

        $subscriptionRepository->findOneByProductId($productId)->willReturn($subscription);
        $subscription->getSubscriptionId()->willReturn($subscriptionId);

        $subscriptionProvider->unsubscribe($subscriptionId)->shouldBeCalled();
        $subscriptionRepository->delete($subscription)->shouldBeCalled();

        $command = new UnsubscribeProductCommand($productId);
        $this->handle($command)->shouldReturn(null);
    }
}
