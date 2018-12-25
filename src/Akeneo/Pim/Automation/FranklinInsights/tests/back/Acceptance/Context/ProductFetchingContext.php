<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Context;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\FetchProductsCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\FetchProductsHandler;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\FakeClient;
use Behat\Behat\Context\Context;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
final class ProductFetchingContext implements Context
{
    /** @var FetchProductsHandler */
    private $fetchProductsHandler;

    /** @var FakeClient */
    private $fakeClient;

    /**
     * @param FetchProductsHandler $fetchProductsHandler
     * @param FakeClient $fakeClient
     */
    public function __construct(
        FetchProductsHandler $fetchProductsHandler,
        FakeClient $fakeClient
    ) {
        $this->fetchProductsHandler = $fetchProductsHandler;
        $this->fakeClient = $fakeClient;
    }

    /**
     * @When the subscribed products are fetched from Franklin
     */
    public function theProductsAreFetchedFromFranklin(): void
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
        $this->fakeClient->defineLastFetchDate($lastFetchDate);
    }
}
