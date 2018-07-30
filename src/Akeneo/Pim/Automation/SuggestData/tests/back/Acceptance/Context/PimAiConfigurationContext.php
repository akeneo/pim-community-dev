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

use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidConnectionConfigurationException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Component\Service\ActivateSuggestDataConnection;
use Akeneo\Pim\Automation\SuggestData\Component\Service\GetNormalizedConfiguration;
use Akeneo\Pim\Automation\SuggestData\Component\Service\GetSuggestDataConnectionStatus;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class PimAiConfigurationContext implements Context
{
    private const PIM_AI_VALID_TOKEN = 'the-only-valid-token-for-acceptance';

    /** @var ActivateSuggestDataConnection */
    private $pimAiConnection;

    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var GetNormalizedConfiguration */
    private $getNormalizedConfiguration;

    /** @var GetSuggestDataConnectionStatus */
    private $getConnectionStatus;

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
    }

    /**
     * @Given Akeneo PIM is not connected to PIM.ai anymore
     */
    public function pimAiIsNotActiveAnymore(): void
    {
        $configuration = new Configuration('pim-ai', ['token' => 'invalid-token']);
        $this->configurationRepository->save($configuration);

        $isActive = $this->getConnectionStatus->forCode('pim-ai');
        Assert::assertFalse($isActive);
    }

    /**
     * @Given Akeneo PIM is connected to PIM.ai
     * @When /^a system administrator tries to (re)?connect Akeneo PIM to PIM.ai$/
     */
    public function activatePimAi(): void
    {
        $success = $this->activatePimAiConnection(static::PIM_AI_VALID_TOKEN);

        Assert::assertTrue($success);
    }

    /**
     * @When a system administrator tries to connect Akeneo PIM to PIM.ai with an invalid activation code
     */
    public function tryToActivatePimAiWithInvalidToken(): void
    {
        $success = $this->activatePimAiConnection('foobar');

        Assert::assertFalse($success);
    }

    /**
     * @param string|null $not
     *
     * @Then /^Akeneo PIM connection to PIM.ai is( not)? activate$/
     */
    public function isPimAiActivated(string $not = null)
    {
        $isActive = $this->getConnectionStatus->forCode('pim-ai');

        if (null === $not) {
            Assert::assertTrue($isActive);
        } else {
            Assert::assertFalse($isActive);
        }
    }

    /**
     * @Then PIM.ai configuration can be retrieved
     */
    public function configurationCanBeRetrieved()
    {
        $configuration = $this->getNormalizedConfiguration->fromCode('pim-ai');

        Assert::assertSame([
            'code' => 'pim-ai',
            'values' => [
                'token' => static::PIM_AI_VALID_TOKEN,
            ],
        ], $configuration);
    }

    /**
     * @param string $activationCode
     *
     * @return bool
     */
    private function activatePimAiConnection(string $activationCode): bool
    {
        try {
            $this->pimAiConnection->activate('pim-ai', ['token' => $activationCode]);
        } catch (InvalidConnectionConfigurationException $exception) {
            return false;
        }

        return true;
    }
}
