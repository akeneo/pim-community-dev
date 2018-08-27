<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\SuggestData\Acceptance\Context;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\FetchProductsCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\FetchProductsHandler;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\SubscriptionFake;
use Behat\Behat\Context\Context;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
final class ProductFetchContext implements Context
{
    /** @var FetchProductsHandler */
    private $fetchProductsHandler;

    /** @var SubscriptionFake */
    private $subscriptionFake;

    /**
     * @param FetchProductsHandler $fetchProductsHandler
     * @param SubscriptionFake $subscriptionFake
     */
    public function __construct(
        FetchProductsHandler $fetchProductsHandler,
        SubscriptionFake $subscriptionFake
    ) {
        $this->fetchProductsHandler = $fetchProductsHandler;
        $this->subscriptionFake = $subscriptionFake;
    }

    /**
     * @When the subscribed products are fetched from PIM.ai
     */
    public function theProductsAreFetchedFromPimAi()
    {
        $this->fetchProductsHandler->handle(new FetchProductsCommand());
    }

    /**
     * @param $lastFetchDate
     *
     * @Given last fetch of subscribed products has been done :lastFetchDate
     */
    public function lastFetchHaveBeenDone($lastFetchDate)
    {
        // TODO: Rework with a real date later
        $this->subscriptionFake->defineLastFetchDate($lastFetchDate);
    }
}
