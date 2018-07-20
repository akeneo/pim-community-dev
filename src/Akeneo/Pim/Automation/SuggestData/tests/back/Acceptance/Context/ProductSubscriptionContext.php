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

namespace Akeneo\Test\Pim\Automation\SuggestData\Acceptance\Context;

use Behat\Behat\Context\Context;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionContext implements Context
{
    /**
     * @When I subscribe the product :identifier to PIM.ai
     */
    public function iSubscribeTheProductToPimAi()
    {

    }

    /**
     * @Then the product :identifier should be subscribed
     */
    public function theProductShouldBeSubscribed()
    {

    }
}
