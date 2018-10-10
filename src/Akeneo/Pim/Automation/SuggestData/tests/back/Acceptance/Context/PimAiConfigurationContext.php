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
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidConnectionConfigurationException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class PimAiConfigurationContext implements Context
{
    private const PIM_AI_VALID_TOKEN = 'valid-token';

    private const PIM_AI_INVALID_TOKEN = 'invalid-token';

    /** @var ActivateConnectionHandler */
    private $activateConnectionHandler;

    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var GetConfigurationHandler */
    private $getConfigurationHandler;

    /** @var GetConnectionStatusHandler */
    private $getConnectionStatusHandler;

    /**
     * Make this context statefull. Useful for testing configuration retrieval.
     *
     * @var ConnectionStatus
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
    }

    /**
     * @Given PIM.ai has not been configured
     */
    public function pimAiHasNotBeenConfigured(): void
    {
        $configuration = $this->configurationRepository->find();
        Assert::assertNull($configuration);
    }

    /**
     * @Given PIM.ai is configured with a valid token
     */
    public function pimAiIsConfiguredWithValidToken(): void
    {
        $configuration = new Configuration(['token' => static::PIM_AI_VALID_TOKEN]);
        $this->configurationRepository->save($configuration);
    }

    /**
     * @Given PIM.ai is configured with an expired token
     */
    public function pimAiIsConfiguredWithAnExpiredToken(): void
    {
        $configuration = new Configuration(['token' => static::PIM_AI_INVALID_TOKEN]);
        $this->configurationRepository->save($configuration);
    }

    /**
     * @When a system administrator configures PIM.ai using a valid token
     */
    public function configuresPimAiUsingValidToken(): void
    {
        $success = $this->activatePimAiConnection(static::PIM_AI_VALID_TOKEN);
        Assert::assertTrue($success);
    }

    /**
     * @When a system administrator configures PIM.ai using an invalid token
     */
    public function configuresPimAiUsingAnInvalidToken(): void
    {
        $success = $this->activatePimAiConnection(static::PIM_AI_INVALID_TOKEN);
        Assert::assertFalse($success);
    }

    /**
     * @When a system administrator retrieves the PIM.ai configuration
     */
    public function retrievesTheConfiguration(): void
    {
        $this->retrievedConfiguration = $this->getConfigurationHandler->handle(new GetConfigurationQuery());
    }

    /**
     * @Then PIM.ai is activated
     */
    public function pimAiIsActivated(): void
    {
        $connectionStatus = $this->getConnectionStatusHandler->handle(new GetConnectionStatusQuery());
        Assert::assertTrue($connectionStatus->isActive());
    }

    /**
     * @Then PIM.ai is not activated
     */
    public function pimAiIsNotActivated(): void
    {
        $connectionStatus = $this->getConnectionStatusHandler->handle(new GetConnectionStatusQuery());
        Assert::assertFalse($connectionStatus->isActive());
    }

    /**
     * @Then PIM.ai valid token is retrieved
     */
    public function aValidTokenIsRetrieved(): void
    {
        Assert::assertEquals(static::PIM_AI_VALID_TOKEN, $this->retrievedConfiguration->getToken());
    }

    /**
     * @Then PIM.ai expired token is retrieved
     */
    public function anExpiredTokenIsRetrieved(): void
    {
        Assert::assertEquals(static::PIM_AI_INVALID_TOKEN, $this->retrievedConfiguration->getToken());
    }

    /**
     * @param string $token
     *
     * @return bool
     */
    private function activatePimAiConnection(string $token): bool
    {
        try {
            $command = new ActivateConnectionCommand(new Token($token));
            $this->activateConnectionHandler->handle($command);
        } catch (InvalidConnectionConfigurationException $exception) {
            return false;
        }

        return true;
    }
}
