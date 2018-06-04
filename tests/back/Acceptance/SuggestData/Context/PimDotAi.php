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

namespace AkeneoEnterprise\Test\Acceptance\SuggestData\Context;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use PimEnterprise\Component\SuggestData\Application\SuggestDataConnection;
use PimEnterprise\Component\SuggestData\Query\GetNormalizedConfiguration;
use PimEnterprise\Component\SuggestData\Repository\ConfigurationRepositoryInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class PimDotAi implements Context
{
    private const PIM_AI_VALID_TOKEN = 'the-only-valid-token-for-acceptance';

    /** @var SuggestDataConnection */
    private $pimDotAiConnection;

    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var GetNormalizedConfiguration */
    private $getNormalizedConfiguration;

    /**
     * @param SuggestDataConnection            $pimDotAiConnection
     * @param ConfigurationRepositoryInterface $configurationRepository
     * @param GetNormalizedConfiguration       $getNormalizedConfiguration
     */
    public function __construct(
        SuggestDataConnection $pimDotAiConnection,
        ConfigurationRepositoryInterface $configurationRepository,
        GetNormalizedConfiguration $getNormalizedConfiguration
    ) {
        $this->pimDotAiConnection = $pimDotAiConnection;
        $this->configurationRepository = $configurationRepository;
        $this->getNormalizedConfiguration = $getNormalizedConfiguration;
    }

    /**
     * @When a valid activation code is used to activate PIM.ai connection
     */
    public function tryToActivatePimDotAiWithValidToken(): void
    {
        $success = $this->activatePimDotAi(static::PIM_AI_VALID_TOKEN);

        Assert::assertTrue($success);
    }

    /**
     * @When an invalid activation code is used to activate PIM.ai connection
     */
    public function tryToActivatePimDotAiWithInvalidToken(): void
    {
        $success = $this->activatePimDotAi('foobar');

        Assert::assertFalse($success);
    }

    /**
     * @When PIM.ai connection is activated
     */
    public function pimDotAiConnectionIsActivated(): void
    {
        $this->activatePimDotAi(static::PIM_AI_VALID_TOKEN);
    }

    /**
     * @param string|null $not
     *
     * @Then /^the PIM.ai connection is( not)? activated$/
     */
    public function isPimDotAiActivated(string $not = null)
    {
        $isActive = $this->configurationRepository->find('pim-dot-ai');

        if (null === $not) {
            Assert::assertNotNull($isActive);
        } else {
            Assert::assertNull($isActive);
        }
    }

    /**
     * @Then I can retrieve the configuration of PIM.ai connection
     */
    public function retrievePimDotAiConnectionConfiguration()
    {
        $configuration = $this->getNormalizedConfiguration->query('pim-dot-ai');

        Assert::assertSame([
            'code' => 'pim-dot-ai',
            'configuration_fields' => [
                'pim_dot_ai_activation_code' => static::PIM_AI_VALID_TOKEN,
            ],
        ], $configuration);
    }

    /**
     * @param string $activationCode
     *
     * @return bool
     */
    private function activatePimDotAi(string $activationCode): bool
    {
        return $this->pimDotAiConnection->activate('pim-dot-ai', ['pim_dot_ai_activation_code' => $activationCode]);
    }
}
