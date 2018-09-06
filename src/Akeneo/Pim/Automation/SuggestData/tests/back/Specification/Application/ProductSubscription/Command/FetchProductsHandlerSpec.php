<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\FetchProductsCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\FetchProductsHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionsResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter\PimAI;
use PhpSpec\ObjectBehavior;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class FetchProductsHandlerSpec extends ObjectBehavior
{
    public function let(
        DataProviderFactory $dataProviderFactory,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        PimAI $dataProvider
    ) {
        $this->beConstructedWith(
            $dataProviderFactory,
            $subscriptionRepository
        );
        $dataProviderFactory->create()->willReturn($dataProvider);
    }

    public function it_is_a_fetch_products_handler()
    {
        $this->shouldHaveType(FetchProductsHandler::class);
    }

    public function it_fetches_products_throught_the_data_provider(
        $dataProvider,
        $subscriptionRepository,
        FetchProductsCommand $command,
        ProductSubscription $subscription
    ) {
        $subscriptionResponse1 = new ProductSubscriptionResponse(42, 'subscription-id', ['foo' => 'bar']);
        $subscriptionsResponse = new ProductSubscriptionsResponse([$subscriptionResponse1]);

        $dataProvider->fetch()->willReturn($subscriptionsResponse);
        $subscriptionRepository->findOneByProductId(42)->willReturn($subscription);
        $subscription->setSuggestedData(new SuggestedData(['foo' => 'bar']))->shouldBeCalled();
        $subscriptionRepository->save($subscription)->shouldBeCalled();

        $this->handle($command)->shouldReturn(null);
    }
}
