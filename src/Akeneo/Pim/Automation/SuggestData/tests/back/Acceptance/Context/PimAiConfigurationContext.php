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

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Service\ActivateSuggestDataConnection;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Service\GetNormalizedConfiguration;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Service\GetSuggestDataConnectionStatus;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidConnectionConfigurationException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
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

    /** @var ActivateSuggestDataConnection */
    private $pimAiConnection;

    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var GetNormalizedConfiguration */
    private $getNormalizedConfiguration;

    /** @var GetSuggestDataConnectionStatus */
    private $getConnectionStatus;

    /**
     * Make this context statefull. Useful for testing configuration retrieval.
     *
     * @var null|array
     */
    private $retrievedConfiguration;

    /**
     * @param ActivateSuggestDataConnection $pimAiConnection
     * @param ConfigurationRepositoryInterface $configurationRepository
     * @param GetNormalizedConfiguration $getNormalizedConfiguration
     * @param GetSuggestDataConnectionStatus $getConnectionStatus
     */
    public function __construct(
        ActivateSuggestDataConnection $pimAiConnection,
        ConfigurationRepositoryInterface $configurationRepository,
        GetNormalizedConfiguration $getNormalizedConfiguration,
        GetSuggestDataConnectionStatus $getConnectionStatus
    ) {
        $this->pimAiConnection = $pimAiConnection;
        $this->configurationRepository = $configurationRepository;
        $this->getNormalizedConfiguration = $getNormalizedConfiguration;
        $this->getConnectionStatus = $getConnectionStatus;
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
        $this->retrievedConfiguration = $this->getNormalizedConfiguration->retrieve();
    }

    /**
     * @Then PIM.ai is activated
     */
    public function pimAiIsActivated(): void
    {
        $connectionStatus = $this->getConnectionStatus->getStatus();
        Assert::assertTrue($connectionStatus->isActive());
    }

    /**
     * @Then PIM.ai is not activated
     */
    public function pimAiIsNotActivated(): void
    {
        $connectionStatus = $this->getConnectionStatus->getStatus();
        Assert::assertFalse($connectionStatus->isActive());
    }

    /**
     * @Then PIM.ai valid token is retrieved
     */
    public function aValidTokenIsRetrieved(): void
    {
        $this->assertPimAiConfigurationEqualsTo(static::PIM_AI_VALID_TOKEN, $this->retrievedConfiguration);
    }

    /**
     * @Then PIM.ai expired token is retrieved
     */
    public function anExpiredTokenIsRetrieved(): void
    {
        $this->assertPimAiConfigurationEqualsTo(static::PIM_AI_INVALID_TOKEN, $this->retrievedConfiguration);
    }

    /**
     * @param string $expectedToken
     * @param array $expectedConfiguration
     */
    private function assertPimAiConfigurationEqualsTo(string $expectedToken, array $expectedConfiguration): void
    {
        Assert::assertSame([
            'code' => Configuration::PIM_AI_CODE,
            'values' => [
                'token' => $expectedToken,
            ],
        ], $expectedConfiguration);
    }

    /**
     * @param string $token
     *
     * @return bool
     */
    private function activatePimAiConnection(string $token): bool
    {
        try {
            $this->pimAiConnection->activate(['token' => $token]);
        } catch (InvalidConnectionConfigurationException $exception) {
            return false;
        }

        return true;
    }
}
