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

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Command\ActivateConnectionCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Command\ActivateConnectionHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConfigurationHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConfigurationQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Exception\ConnectionConfigurationException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\FakeClient;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class FranklinConfigurationContext implements Context
{
    /** @var ActivateConnectionHandler */
    private $activateConnectionHandler;

    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var GetConfigurationHandler */
    private $getConfigurationHandler;

    /**
     * Make this context stateful. Useful for testing configuration retrieval.
     *
     * @var Configuration
     */
    private $retrievedConfiguration;

    /**
     * @param ActivateConnectionHandler $activateConnectionHandler
     * @param GetConfigurationHandler $getConfigurationHandler
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(
        ActivateConnectionHandler $activateConnectionHandler,
        GetConfigurationHandler $getConfigurationHandler,
        ConfigurationRepositoryInterface $configurationRepository
    ) {
        $this->activateConnectionHandler = $activateConnectionHandler;
        $this->getConfigurationHandler = $getConfigurationHandler;
        $this->configurationRepository = $configurationRepository;
        $this->retrievedConfiguration = null;
    }

    /**
     * @Given Franklin has not been configured
     */
    public function franklinHasNotBeenConfigured(): void
    {
        $configuration = $this->configurationRepository->find();
        Assert::isInstanceOf($configuration, Configuration::class);
        Assert::null($configuration->getToken());
    }

    /**
     * @Given Franklin is configured with a valid token
     */
    public function franklinIsConfiguredWithAValidToken(): void
    {
        $configuration = new Configuration();
        $configuration->setToken(new Token(FakeClient::VALID_TOKEN));
        $this->configurationRepository->save($configuration);
    }

    /**
     * @Given Franklin is configured with an expired token
     */
    public function franklinIsConfiguredWithAnExpiredToken(): void
    {
        $configuration = new Configuration();
        $configuration->setToken(new Token(FakeClient::INVALID_TOKEN));
        $this->configurationRepository->save($configuration);
    }

    /**
     * @When I configure Franklin using a valid token
     */
    public function iConfigureFranklinUsingAValidToken(): void
    {
        try {
            $command = new ActivateConnectionCommand(new Token(FakeClient::VALID_TOKEN));
            $this->activateConnectionHandler->handle($command);
        } catch (ConnectionConfigurationException $e) {
            ExceptionContext::setThrownException($e);
        }
    }

    /**
     * @When I configure Franklin using an invalid token
     */
    public function iConfiguresFranklinUsingAnInvalidToken(): void
    {
        try {
            $command = new ActivateConnectionCommand(new Token(FakeClient::INVALID_TOKEN));
            $this->activateConnectionHandler->handle($command);
        } catch (ConnectionConfigurationException $e) {
            ExceptionContext::setThrownException($e);
        }
    }

    /**
     * @When I retrieve Franklin's configuration
     */
    public function iRetrieveFranklinsConfiguration(): void
    {
        $this->retrievedConfiguration = $this->getConfigurationHandler->handle(new GetConfigurationQuery());
    }

    /**
     * @Then Franklin should be activated
     */
    public function franklinShouldBeActivated(): void
    {
        $configuration = $this->configurationRepository->find();
        Assert::eq($configuration->getToken(), FakeClient::VALID_TOKEN);
    }

    /**
     * @Then Franklin should not be activated
     */
    public function franklinShouldNotBeActivated(): void
    {
        $configuration = $this->configurationRepository->find();
        Assert::notEq($configuration->getToken(), FakeClient::VALID_TOKEN);
    }

    /**
     * @Then the retrieved token should be valid
     */
    public function theRetrievedTokenShouldBeValid(): void
    {
        Assert::eq(FakeClient::VALID_TOKEN, $this->retrievedConfiguration->getToken());
    }

    /**
     * @Then the retrieved token should be expired
     */
    public function theRetrievedTokenShouldBeExpired(): void
    {
        Assert::eq(FakeClient::INVALID_TOKEN, $this->retrievedConfiguration->getToken());
    }

    /**
     * @Then no token should be retrieved
     */
    public function noTokenShouldBeRetrieved(): void
    {
        $configuration = $this->configurationRepository->find();
        Assert::isInstanceOf($configuration, Configuration::class);
        Assert::null($configuration->getToken());
    }

    /**
     * @Then a token invalid message for configuration should be sent
     */
    public function aTokenInvalidMessageForConfigurationShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::isInstanceOf($thrownException, ConnectionConfigurationException::class);
        Assert::eq(
            ConnectionConfigurationException::invalidToken()->getMessage(),
            $thrownException->getMessage()
        );
        Assert::eq(422, $thrownException->getCode());
    }

    /**
     * @Then a connection invalid message should be sent
     * TODO: DUPLICATE
     */
    public function aConnectionInvalidMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::isInstanceOf($thrownException, ConnectionConfigurationException::class);
        Assert::eq(
            ConnectionConfigurationException::invalidToken()->getMessage(),
            $thrownException->getMessage()
        );
        Assert::eq(422, $thrownException->getCode());
    }
}
