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

namespace Akeneo\ReferenceEntity\Integration\Connector\Distribution;

use Behat\Behat\Context\Context;
use PhpSpec\Exception\Example\PendingException;

class GetConnectorReferenceEntity implements Context
{
    /**
     * @Given /^the Brand reference entity$/
     */
    public function theBrandReferenceEntity(): void
    {
        throw new PendingException();
    }

    /**
     * @When /^the connector requests the Brand reference entity$/
     */
    public function theConnectorRequestsTheBrandReferenceEntity(): void
    {
        throw new PendingException();
    }

    /**
     * @Then /^the PIM returns the Brand reference entity$/
     */
    public function thePIMReturnsTheBrandReferenceEntity(): void
    {
        throw new PendingException();
    }
}
