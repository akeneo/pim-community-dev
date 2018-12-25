<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\FetchProductsCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\FetchProductsHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\SubscriptionsCursor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FetchProductsHandlerSpec extends ObjectBehavior
{
    public function let(
        SubscriptionProviderInterface $subscriptionProvider,
        ProductSubscriptionRepositoryInterface $subscriptionRepository
    ): void {
        $this->beConstructedWith(
            $subscriptionProvider,
            $subscriptionRepository
        );
    }

    public function it_is_a_fetch_products_handler(): void
    {
        $this->shouldHaveType(FetchProductsHandler::class);
    }

    public function it_fetches_subscriptions_through_the_data_provider_and_saves_them(
        $subscriptionProvider,
        $subscriptionRepository,
        FetchProductsCommand $command,
        ProductSubscription $subscription1,
        ProductSubscription $subscription2,
        SubscriptionsCursor $cursor
    ): void {
        $subscriptionProvider->fetch()->willReturn($cursor);

        $subscriptionResponse1 = new ProductSubscriptionResponse(
            42,
            'an-id',
            [['pimAttributeCode' => 'foo', 'value' => 'bar']],
            true,
            false
        );
        $subscriptionResponse2 = new ProductSubscriptionResponse(
            84,
            'another-id',
            [['pimAttributeCode' => 'foo', 'value' => 'bar']],
            false,
            false
        );

        $cursor->valid()->willReturn(true, true, false);
        $cursor->current()->willReturn($subscriptionResponse1, $subscriptionResponse2);
        $cursor->next()->shouldBeCalledTimes(2);
        $cursor->rewind()->shouldBeCalledTimes(1);

        $subscriptionRepository->findOneByProductId(42)->willReturn($subscription1);
        $subscriptionRepository->findOneByProductId(84)->willReturn($subscription2);
        $subscriptionRepository->save($subscription1)->shouldBeCalled();
        $subscriptionRepository->save($subscription2)->shouldBeCalled();
        $subscriptionRepository->delete(Argument::any())->shouldNotBeCalled();

        $subscription1->setSuggestedData(Argument::type(SuggestedData::class))->shouldBeCalled();
        $subscription1->markAsMissingMapping(true)->shouldBeCalled();

        $subscription2->setSuggestedData(Argument::type(SuggestedData::class))->shouldBeCalled();
        $subscription2->markAsMissingMapping(false)->shouldBeCalled();

        $this->handle($command)->shouldReturn(null);
    }

    public function it_fetches_subscriptions_through_the_data_provider_and_removes_cancelled_ones(
        $subscriptionProvider,
        $subscriptionRepository,
        FetchProductsCommand $command,
        ProductSubscription $subscription1,
        ProductSubscription $subscription2,
        SubscriptionsCursor $cursor
    ): void {
        $subscriptionProvider->fetch()->willReturn($cursor);

        $subscriptionResponse1 = new ProductSubscriptionResponse(
            42,
            'an-id',
            [['pimAttributeCode' => 'foo', 'value' => 'bar']],
            true,
            true
        );
        $subscriptionResponse2 = new ProductSubscriptionResponse(
            84,
            'another-id',
            [['pimAttributeCode' => 'foo', 'value' => 'bar']],
            false,
            true
        );

        $cursor->valid()->willReturn(true, true, false);
        $cursor->current()->willReturn($subscriptionResponse1, $subscriptionResponse2);
        $cursor->next()->shouldBeCalledTimes(2);
        $cursor->rewind()->shouldBeCalledTimes(1);

        $subscriptionRepository->findOneByProductId(42)->willReturn($subscription1);
        $subscriptionRepository->findOneByProductId(84)->willReturn($subscription2);
        $subscriptionRepository->save($subscription1)->shouldNotBeCalled();
        $subscriptionRepository->save($subscription2)->shouldNotBeCalled();
        $subscriptionRepository->delete($subscription1)->shouldBeCalled();
        $subscriptionRepository->delete($subscription2)->shouldBeCalled();

        $this->handle($command)->shouldReturn(null);
    }
}
