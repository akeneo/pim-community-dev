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
final class ProductFetchingContext implements Context
{
    /** @var FetchProductsHandler */
    private $fetchProductsHandler;

    /** @var SubscriptionFake */
    private $subscriptionApi;

    /**
     * @param FetchProductsHandler $fetchProductsHandler
     * @param SubscriptionFake $subscriptionFake
     */
    public function __construct(
        FetchProductsHandler $fetchProductsHandler,
        SubscriptionFake $subscriptionApi
    ) {
        $this->fetchProductsHandler = $fetchProductsHandler;
        $this->subscriptionApi = $subscriptionApi;
    }

    /**
     * @When the subscribed products are fetched from PIM.ai
     */
    public function theProductsAreFetchedFromPimAi(): void
    {
        try {
            $this->fetchProductsHandler->handle(new FetchProductsCommand());
        } catch (\Exception $e) {
        }
    }

    /**
     * @param mixed $lastFetchDate
     *
     * @Given last fetch of subscribed products has been done :lastFetchDate
     */
    public function lastFetchHaveBeenDone($lastFetchDate): void
    {
        // TODO: Rework with a real date later (See APAI-170)
        $this->subscriptionApi->defineLastFetchDate($lastFetchDate);
    }
}
