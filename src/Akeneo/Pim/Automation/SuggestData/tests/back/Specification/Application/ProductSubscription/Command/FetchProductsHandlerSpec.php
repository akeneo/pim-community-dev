<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\FetchProductsCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\FetchProductsHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionsResponse;
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
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        PimAI $dataProvider
    ) {
        $this->beConstructedWith(
            $dataProviderFactory,
            $productSubscriptionRepository
        );
        $dataProviderFactory->create()->willReturn($dataProvider);
    }

    public function it_is_a_fetch_products_handler()
    {
        $this->shouldHaveType(FetchProductsHandler::class);
    }

    public function it_fetches_products_throught_the_data_provider(
        $dataProvider,
        FetchProductsCommand $command,
        ProductSubscriptionsResponse $subscribedResponses
    ) {
        $dataProvider->fetch()->willReturn($subscribedResponses);

        $this->handle($command)->shouldReturn(null);
    }
}
