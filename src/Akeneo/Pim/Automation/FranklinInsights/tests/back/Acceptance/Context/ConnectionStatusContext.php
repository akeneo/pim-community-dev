<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Context;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class ConnectionStatusContext implements Context
{
    /** @var GetConnectionStatusHandler */
    private $getConnectionStatusHandler;

    /** @var ConnectionStatus */
    private $retrievedConnectionStatus;

    /**
     * @param GetConnectionStatusHandler $getConnectionStatusHandler
     */
    public function __construct(GetConnectionStatusHandler $getConnectionStatusHandler)
    {
        $this->getConnectionStatusHandler = $getConnectionStatusHandler;
    }

    /**
     * @When I retrieve the connection status
     */
    public function iRetrieveTheConnectionStatus(): void
    {
        try {
            $query = new GetConnectionStatusQuery();
            $this->retrievedConnectionStatus = $this->getConnectionStatusHandler->handle($query);
        } catch (\Exception $e) {
            ExceptionContext::setThrownException($e);
        }
    }

    /**
     * @Then the identifiers mapping should be valid
     */
    public function theIdentifiersMappingShouldBeValid(): void
    {
        Assert::true($this->retrievedConnectionStatus->isIdentifiersMappingValid());
    }

    /**
     * @Then the identifiers mapping should not be valid
     */
    public function theIdentifiersMappingShouldNotBeValid(): void
    {
        Assert::false($this->retrievedConnectionStatus->isIdentifiersMappingValid());
    }

    /**
     * @Then Franklin connection status should be activated
     */
    public function franklinConnectionStatusShouldBeActivated(): void
    {
        Assert::true($this->retrievedConnectionStatus->isActive());
    }

    /**
     * @Then Franklin connection status should not be activated
     */
    public function franklinConnectionStatusShouldNotBeActivated(): void
    {
        Assert::false($this->retrievedConnectionStatus->isActive());
    }

    /**
     * @Then there should have :expectedCount product(s) subscribed to Franklin
     *
     * @param int $expectedCount
     */
    public function thereShouldHaveProductsSubscribedToFranklin($expectedCount): void
    {
        Assert::eq($this->retrievedConnectionStatus->productSubscriptionCount(), $expectedCount);
    }
}
