<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\FetchProductsCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\FetchProductsHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter\PimAI;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\SubscriptionsCursor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FetchProductsHandlerSpec extends ObjectBehavior
{
    public function let(
        DataProviderFactory $dataProviderFactory,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        PimAI $dataProvider
    ): void {
        $this->beConstructedWith(
            $dataProviderFactory,
            $subscriptionRepository
        );
        $dataProviderFactory->create()->willReturn($dataProvider);
    }

    public function it_is_a_fetch_products_handler(): void
    {
        $this->shouldHaveType(FetchProductsHandler::class);
    }

    public function it_fetches_products_through_the_data_provider(
        $dataProvider,
        $subscriptionRepository,
        FetchProductsCommand $command,
        ProductSubscription $subscription1,
        ProductSubscription $subscription2,
        SubscriptionsCursor $cursor
    ): void {
        $dataProvider->fetch()->willReturn($cursor);

        $subscriptionResponse1 = new ProductSubscriptionResponse(42, 'an-id', ['foo' => 'bar']);
        $subscriptionResponse2 = new ProductSubscriptionResponse(84, 'another-id', ['foo' => 'bar']);

        $cursor->valid()->willReturn(true, true, false);
        $cursor->current()->willReturn($subscriptionResponse1, $subscriptionResponse2);
        $cursor->next()->shouldBeCalledTimes(2);
        $cursor->rewind()->shouldBeCalledTimes(1);

        $subscriptionRepository->findOneByProductId(42)->willReturn($subscription1);
        $subscriptionRepository->findOneByProductId(84)->willReturn($subscription2);
        $subscriptionRepository->save($subscription1)->shouldBeCalled();
        $subscriptionRepository->save($subscription2)->shouldBeCalled();

        $subscription1->setSuggestedData(Argument::type(SuggestedData::class))->shouldBeCalled();
        $subscription2->setSuggestedData(Argument::type(SuggestedData::class))->shouldBeCalled();

        $this->handle($command)->shouldReturn(null);
    }
}
