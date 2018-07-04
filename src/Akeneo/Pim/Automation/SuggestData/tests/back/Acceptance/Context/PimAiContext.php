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

namespace Akeneo\Pim\Automation\SuggestData\tests\back\Acceptance\Context;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Akeneo\Pim\Automation\SuggestData\Component\Application\ActivateSuggestDataConnection;
use Akeneo\Pim\Automation\SuggestData\Component\Application\GetNormalizedConfiguration;
use Akeneo\Pim\Automation\SuggestData\Component\Repository\ConfigurationRepositoryInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class PimAiContext implements Context
{
    private const PIM_AI_VALID_TOKEN = 'the-only-valid-token-for-acceptance';

    /** @var ActivateSuggestDataConnection */
    private $pimAiConnection;

    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var GetNormalizedConfiguration */
    private $getNormalizedConfiguration;

    /**
     * @param ActivateSuggestDataConnection    $pimAiConnection
     * @param ConfigurationRepositoryInterface $configurationRepository
     * @param GetNormalizedConfiguration       $getNormalizedConfiguration
     */
    public function __construct(
        ActivateSuggestDataConnection $pimAiConnection,
        ConfigurationRepositoryInterface $configurationRepository,
        GetNormalizedConfiguration $getNormalizedConfiguration
    ) {
        $this->pimAiConnection = $pimAiConnection;
        $this->configurationRepository = $configurationRepository;
        $this->getNormalizedConfiguration = $getNormalizedConfiguration;
    }

    /**
     * @When a valid activation code is used to activate PIM.ai connection
     */
    public function tryToActivatePimAiWithValidToken(): void
    {
        $success = $this->activatePimAi(static::PIM_AI_VALID_TOKEN);

        Assert::assertTrue($success);
    }

    /**
     * @When an invalid activation code is used to activate PIM.ai connection
     */
    public function tryToActivatePimAiWithInvalidToken(): void
    {
        $success = $this->activatePimAi('foobar');

        Assert::assertFalse($success);
    }

    /**
     * @When PIM.ai connection is activated
     */
    public function pimAiConnectionIsActivated(): void
    {
        $this->activatePimAi(static::PIM_AI_VALID_TOKEN);
    }

    /**
     * @param string|null $not
     *
     * @Then /^the PIM.ai connection is( not)? activated$/
     */
    public function isPimAiActivated(string $not = null)
    {
        $isActive = $this->configurationRepository->findOneByCode('pim-ai');

        if (null === $not) {
            Assert::assertNotNull($isActive);
        } else {
            Assert::assertNull($isActive);
        }
    }

    /**
     * @Then I can retrieve the configuration of PIM.ai connection
     */
    public function retrievePimAiConnectionConfiguration()
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
    private function activatePimAi(string $activationCode): bool
    {
        return $this->pimAiConnection->activate('pim-ai', ['token' => $activationCode]);
    }
}
