<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UpdateSubscriptionFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UpdateSubscriptionFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\Family;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UpdateSubscriptionFamilyHandlerSpec extends ObjectBehavior
{
    public function let(
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        SubscriptionProviderInterface $subscriptionProvider
    ): void {
        $this->beConstructedWith($productSubscriptionRepository, $subscriptionProvider);
    }

    public function it_is_an_update_subscription_family_handler(): void
    {
        $this->shouldHaveType(UpdateSubscriptionFamilyHandler::class);
    }

    public function it_does_nothing_if_the_product_is_not_subscribed(
        $productSubscriptionRepository,
        $subscriptionProvider
    ): void {
        $productSubscriptionRepository->findOneByProductId(42)->willReturn(null);
        $subscriptionProvider->updateFamilyInfos(Argument::cetera())->shouldNotBeCalled();

        $this->handle(new UpdateSubscriptionFamilyCommand(42, new Family()));
    }

    public function it_updates_family_infos_for_a_subscribed_product(
        $productSubscriptionRepository,
        $subscriptionProvider
    ): void {
        $productSubscriptionRepository->findOneByProductId(42)->willReturn(
            new ProductSubscription(42, '123456-abcdef', [])
        );

        $family = new Family();
        $subscriptionProvider->updateFamilyInfos('123456-abcdef', $family)->shouldBeCalled();

        $this->handle(new UpdateSubscriptionFamilyCommand(42, $family));
    }
}
