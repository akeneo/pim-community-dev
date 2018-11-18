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

namespace Akeneo\Test\Pim\Automation\SuggestData\EndToEnd\Context;

use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;
use Webmozart\Assert\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class FranklinConfigurationContext extends PimContext
{
    use SpinCapableTrait;

    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /**
     * @param string $mainContextClass
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(string $mainContextClass, ConfigurationRepositoryInterface $configurationRepository)
    {
        parent::__construct($mainContextClass);

        $this->configurationRepository = $configurationRepository;
    }

    /**
     * @Given Franklin has not been configured
     */
    public function franklinHasNotBeenConfigured(): void
    {
        $configuration = $this->configurationRepository->find();

        Assert::null($configuration->getToken());
    }

    /**
     * @When a system administrator configures Franklin using a valid token
     *
     * @throws \Context\Spin\TimeoutException
     * @throws \Exception
     */
    public function franklinIsConfiguredWithValidToken(): void
    {
        $this->loadDefaultCatalog();
        $this->loginAsAdmin();
        $this->configureValidToken();
    }

    /**
     * @Then Franklin is activated
     *
     * @throws \Context\Spin\TimeoutException
     */
    public function franklinIsActivated(): void
    {
        $this->checkFranklinConfigurationIsSaved();
        $this->checkActivationButtonIsGreen();
    }

    /**
     * @throws \Exception
     */
    private function loadDefaultCatalog(): void
    {
        $this
            ->getMainContext()
            ->getSubcontext('catalogConfiguration')
            ->aCatalogConfiguration('default');
    }

    private function loginAsAdmin(): void
    {
        $this->getNavigationContext()->iAmLoggedInAs('admin', 'admin');
    }

    /**
     * @throws \Context\Spin\TimeoutException
     */
    private function configureValidToken(): void
    {
        $this->getNavigationContext()->iAmOnThePage('Franklin configuration');

        $this->spin(function (): bool {
            if (null === $this->getCurrentPage()->find('css', '.token-field')) {
                return false;
            }

            $this->getCurrentPage()->fillField('token', 'valid-token');

            return true;
        }, 'Impossible to fill the "token" field.');

        $this->spin(function () {
            return $this->getCurrentPage()->find('css', '.activate-connection');
        }, 'Impossible to find the Activate button')->click();
    }

    /**
     * @throws \Context\Spin\TimeoutException
     */
    private function checkFranklinConfigurationIsSaved(): void
    {
        $this->spin(function (): bool {
            $configuration = $this->configurationRepository->find();

            if (!$configuration instanceof Configuration) {
                return false;
            }

            return true;
        }, 'There is no Franklin configuration saved.');
    }

    /**
     * @throws \Context\Spin\TimeoutException
     */
    private function checkActivationButtonIsGreen(): void
    {
        $activationButton = $this->spin(function (): ?NodeElement {
            if (null === $activationButton = $this->getCurrentPage()->find('css', '.suggest-data-connection')) {
                return null;
            }

            return $activationButton;
        }, 'Impossible to get the activation button');

        $this->spin(function () use ($activationButton): bool {
            if ('ACTIVATED' !== $activationButton->getText()) {
                return false;
            }

            return true;
        }, 'Activation button does not indicate that the connection to Franklin is active.');
    }
}
