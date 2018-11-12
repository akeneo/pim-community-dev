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

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\ActivateConnectionCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\ActivateConnectionHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConfigurationHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConfigurationQuery;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ConnectionConfigurationException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class FranklinConfigurationContext implements Context
{
    private const FRANKLIN_VALID_TOKEN = 'valid-token';

    private const FRANKLIN_INVALID_TOKEN = 'invalid-token';

    /** @var ActivateConnectionHandler */
    private $activateConnectionHandler;

    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var GetConfigurationHandler */
    private $getConfigurationHandler;

    /** @var GetConnectionStatusHandler */
    private $getConnectionStatusHandler;

    /** @var ConnectionConfigurationException */
    private $thrownException;

    /**
     * Make this context statefull. Useful for testing configuration retrieval.
     *
     * @var Configuration
     */
    private $retrievedConfiguration;

    /**
     * @param ActivateConnectionHandler $activateConnectionHandler
     * @param GetConfigurationHandler $getConfigurationHandler
     * @param GetConnectionStatusHandler $getConnectionStatusHandler
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(
        ActivateConnectionHandler $activateConnectionHandler,
        GetConfigurationHandler $getConfigurationHandler,
        GetConnectionStatusHandler $getConnectionStatusHandler,
        ConfigurationRepositoryInterface $configurationRepository
    ) {
        $this->activateConnectionHandler = $activateConnectionHandler;
        $this->getConfigurationHandler = $getConfigurationHandler;
        $this->getConnectionStatusHandler = $getConnectionStatusHandler;
        $this->configurationRepository = $configurationRepository;
        $this->retrievedConfiguration = null;
        $this->thrownException = null;
    }

    /**
     * @Given Franklin has not been configured
     */
    public function franklinHasNotBeenConfigured(): void
    {
        $configuration = $this->configurationRepository->find();
        Assert::assertInstanceOf(Configuration::class, $configuration);
        Assert::assertNull($configuration->getToken());
    }

    /**
     * @Given Franklin is configured with a valid token
     */
    public function franklinIsConfiguredWithValidToken(): void
    {
        $configuration = new Configuration();
        $configuration->setToken(new Token(self::FRANKLIN_VALID_TOKEN));
        $this->configurationRepository->save($configuration);
    }

    /**
     * @Given Franklin is configured with an expired token
     */
    public function franklinIsConfiguredWithAnExpiredToken(): void
    {
        $configuration = new Configuration();
        $configuration->setToken(new Token(self::FRANKLIN_INVALID_TOKEN));
        $this->configurationRepository->save($configuration);
    }

    /**
     * @When a system administrator configures Franklin using a valid token
     */
    public function configuresFranklinUsingValidToken(): void
    {
        $command = new ActivateConnectionCommand(new Token(static::FRANKLIN_VALID_TOKEN));
        $this->activateConnectionHandler->handle($command);
    }

    /**
     * @When a system administrator configures Franklin using an invalid token
     */
    public function configuresFranklinUsingAnInvalidToken(): void
    {
        try {
            $command = new ActivateConnectionCommand(new Token(static::FRANKLIN_INVALID_TOKEN));
            $this->activateConnectionHandler->handle($command);

            throw new ConnectionConfigurationException();
        } catch (ConnectionConfigurationException $e) {
            $this->thrownException = $e;
        }
    }

    /**
     * @When a system administrator retrieves the Franklin configuration
     */
    public function retrievesTheConfiguration(): void
    {
        $this->retrievedConfiguration = $this->getConfigurationHandler->handle(new GetConfigurationQuery());
    }

    /**
     * @Then Franklin is activated
     */
    public function franklinIsActivated(): void
    {
        $connectionStatus = $this->getConnectionStatusHandler->handle(new GetConnectionStatusQuery());
        Assert::assertTrue($connectionStatus->isActive());
    }

    /**
     * @Then Franklin is not activated
     */
    public function franklinIsNotActivated(): void
    {
        $connectionStatus = $this->getConnectionStatusHandler->handle(new GetConnectionStatusQuery());
        Assert::assertFalse($connectionStatus->isActive());
    }

    /**
     * @Then identifiers mapping should be valid
     */
    public function identifiersMappingShouldBeValid(): void
    {
        $connectionStatus = $this->getConnectionStatusHandler->handle(new GetConnectionStatusQuery());

        Assert::assertTrue($connectionStatus->isIdentifiersMappingValid());
    }

    /**
     * @Then identifiers mapping should not be valid
     */
    public function identifiersMappingShouldNotBeValid(): void
    {
        $connectionStatus = $this->getConnectionStatusHandler->handle(new GetConnectionStatusQuery());

        Assert::assertFalse($connectionStatus->isIdentifiersMappingValid());
    }

    /**
     * @Then a token invalid message is sent
     */
    public function aTokenInvalidMessageIsSent(): void
    {
        Assert::assertInstanceOf(ConnectionConfigurationException::class, $this->thrownException);
        Assert::assertEquals(
            ConnectionConfigurationException::invalidToken()->getMessage(),
            $this->thrownException->getMessage()
        );
        Assert::assertEquals(422, $this->thrownException->getCode());
    }

    /**
     * @Then Franklin valid token is retrieved
     */
    public function aValidTokenIsRetrieved(): void
    {
        Assert::assertEquals(static::FRANKLIN_VALID_TOKEN, $this->retrievedConfiguration->getToken());
    }

    /**
     * @Then Franklin expired token is retrieved
     */
    public function anExpiredTokenIsRetrieved(): void
    {
        Assert::assertEquals(static::FRANKLIN_INVALID_TOKEN, $this->retrievedConfiguration->getToken());
    }
}
