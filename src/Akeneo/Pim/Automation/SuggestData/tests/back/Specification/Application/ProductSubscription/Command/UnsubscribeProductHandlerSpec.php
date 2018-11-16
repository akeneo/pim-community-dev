<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\ProductSubscription;
use PhpSpec\ObjectBehavior;

class UnsubscribeProductHandlerSpec extends ObjectBehavior
{
    public function let(
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        DataProviderFactory $dataProviderFactory,
        DataProviderInterface $dataProvider
    ): void {
        $this->beConstructedWith(
            $subscriptionRepository,
            $dataProviderFactory
        );
        $dataProviderFactory->create()->willReturn($dataProvider);
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
        $dataProvider,
        ProductSubscription $subscription
    ): void {
        $productId = 42;
        $subscriptionId = 'foo-bar';

        $subscriptionRepository->findOneByProductId($productId)->willReturn($subscription);
        $subscription->getSubscriptionId()->willReturn($subscriptionId);

        $dataProvider->unsubscribe($subscriptionId)->shouldBeCalled();
        $subscriptionRepository->delete($subscription)->shouldBeCalled();

        $command = new UnsubscribeProductCommand($productId);
        $this->handle($command)->shouldReturn(null);
    }
}
