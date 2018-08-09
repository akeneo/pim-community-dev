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

    private const PIM_AI_CONFIGURATION_CODE = 'pim-ai';

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
     * @var null|Configuration
     */
    private $retrievedConfiguration;

    /**
     * @param ActivateSuggestDataConnection    $pimAiConnection
     * @param ConfigurationRepositoryInterface $configurationRepository
     * @param GetNormalizedConfiguration       $getNormalizedConfiguration
     * @param GetSuggestDataConnectionStatus   $getConnectionStatus
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
    public function pimAiHasNotBeenConfigured()
    {
        $configuration = $this->configurationRepository->findOneByCode(static::PIM_AI_CONFIGURATION_CODE);
        Assert::isNull($configuration);
    }

    /**
     * @Given PIM.ai is configured with a valid token
     */
    public function pimAiIsConfiguredWithAValidToken()
    {
        $configuration = new Configuration(static::PIM_AI_CONFIGURATION_CODE, ['token' => static::PIM_AI_VALID_TOKEN]);
        $this->configurationRepository->save($configuration);
    }

    /**
     * @Given PIM.ai is configured with an expired token
     */
    public function pimAiIsConfiguredWithAnExpiredToken(): void
    {
        $configuration = new Configuration(static::PIM_AI_CONFIGURATION_CODE, ['token' => static::PIM_AI_INVALID_TOKEN]);
        $this->configurationRepository->save($configuration);
    }

    /**
     * @When a system administrator configures PIM.ai using a valid token
     */
    public function configuresPimAiUsingAValidToken(): void
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
    public function retrievesTheConfiguration()
    {
        $this->retrievedConfiguration = $this->getNormalizedConfiguration->fromCode(static::PIM_AI_CONFIGURATION_CODE);
    }

    /**
     * @Then PIM.ai is activated
     */
    public function pimAiIsActivated()
    {
        $isActive = $this->getConnectionStatus->forCode(static::PIM_AI_CONFIGURATION_CODE);
        Assert::assertTrue($isActive);
    }

    /**
     * @Then PIM.ai is not activated
     */
    public function pimAiIsNotActivated()
    {
        $isActive = $this->getConnectionStatus->forCode(static::PIM_AI_CONFIGURATION_CODE);
        Assert::assertFalse($isActive);
    }

    /**
     * @Then PIM.ai valid token is retrieved
     */
    public function aValidTokenIsRetrieved()
    {
        $this->assertPimAiConfigurationEqualsTo(static::PIM_AI_VALID_TOKEN, $this->retrievedConfiguration);
    }

    /**
     * @Then PIM.ai expired token is retrieved
     */
    public function anExpiredTokenIsRetrieved()
    {
        $this->assertPimAiConfigurationEqualsTo(static::PIM_AI_INVALID_TOKEN, $this->retrievedConfiguration);
    }

    /**
     * @param string $expectedToken
     * @param array $expectedConfiguration
     */
    private function assertPimAiConfigurationEqualsTo(string $expectedToken, array $expectedConfiguration)
    {
        Assert::assertSame([
            'code' => static::PIM_AI_CONFIGURATION_CODE,
            'values' => [
                'token' => $expectedToken,
            ],
        ], $expectedConfiguration);
    }

    /**
     * @param string $activationCode
     *
     * @return bool
     */
    private function activatePimAiConnection(string $activationCode): bool
    {
        try {
            $this->pimAiConnection->activate(static::PIM_AI_CONFIGURATION_CODE, ['token' => $activationCode]);
        } catch (InvalidConnectionConfigurationException $exception) {
            return false;
        }

        return true;
    }
}
