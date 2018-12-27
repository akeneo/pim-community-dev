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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * Static context to handle exceptions
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
final class ExceptionContext implements Context
{
    /** @var \Exception */
    private static $thrownException;

    public function __construct()
    {
        self::$thrownException = null;
    }

    /**
     * @param \Exception $e
     */
    public static function setThrownException(\Exception $e): void
    {
        self::$thrownException = $e;
    }

    /**
     * @return \Exception|null
     */
    public static function getThrownException(): ?\Exception
    {
        return self::$thrownException;
    }

    /**
     * @Then a data provider error message should be sent
     */
    public function aDataProviderErrorMessageShouldBeSent(): void
    {
        Assert::isInstanceOf(self::$thrownException, DataProviderException::class);
        Assert::eq(
            self::$thrownException->getMessage(),
            DataProviderException::serverIsDown(new \Exception())->getMessage()
        );
    }

    /**
     * @Then an authentication error message should be sent
     */
    public function anAuthenticationErrorMessageShouldBeSent(): void
    {
        Assert::isInstanceOf(self::$thrownException, DataProviderException::class);
        Assert::eq(
            self::$thrownException->getMessage(),
            DataProviderException::authenticationError(new \Exception())->getMessage()
        );
    }

    /**
     * @Then a bad request message should be sent
     */
    public function aBadRequestMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::isInstanceOf($thrownException, DataProviderException::class);
        Assert::eq(
            DataProviderException::badRequestError()->getMessage(),
            $thrownException->getMessage()
        );
    }

    /**
     * @Then an unknown family message should be sent
     */
    public function anUnknownFamilyMessageShouldBeSent(): void
    {
        Assert::isInstanceOf(self::$thrownException, \InvalidArgumentException::class);
    }

    /**
     * @Then an unknown attribute message should be sent
     */
    public function anUnknownAttributeMessageShouldBeSent(): void
    {
        Assert::isInstanceOf(self::$thrownException, \InvalidArgumentException::class);
    }
}
