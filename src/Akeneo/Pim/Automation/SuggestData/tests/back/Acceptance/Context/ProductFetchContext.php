<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\SuggestData\Acceptance\Context;

use Akeneo\Pim\Automation\SuggestData\Application\ProductFetch\Command\FetchProductsCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductFetch\Command\FetchProductsHandler;
use Behat\Behat\Context\Context;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
final class ProductFetchContext implements Context
{
    private $fetchProductsHandler;

    public function __construct(FetchProductsHandler $fetchProductsHandler)
    {
        $this->fetchProductsHandler = $fetchProductsHandler;
    }

    /**
     * @When the subscribed products are fetched from PIM.ai
     */
    public function theProductsAreFetchedFromPimAi()
    {
        $this->fetchProductsHandler->handle(new FetchProductsCommand());
    }
}
