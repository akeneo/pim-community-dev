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

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;
use Webmozart\Assert\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class PimAiConfigurationContext extends PimContext
{
    use SpinCapableTrait;

    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /**
     * @param string                           $mainContextClass
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(string $mainContextClass, ConfigurationRepositoryInterface $configurationRepository)
    {
        parent::__construct($mainContextClass);

        $this->configurationRepository = $configurationRepository;
    }

    /**
     * @Given PIM.ai has not been configured
     */
    public function pimAiHasNotBeenConfigured(): void
    {
        $configuration = $this->configurationRepository->find();

        Assert::null($configuration);
    }

    /**
     * @When a system administrator configures PIM.ai using a valid token
     *
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     * @throws \Context\Spin\TimeoutException
     * @throws \Exception
     */
    public function pimAiIsConfiguredWithAValidToken(): void
    {
        $this->loadDefaultCatalog();
        $this->loginAsAdmin();
        $this->configureValidToken();
    }

    /**
     * @Then PIM.ai is activated
     *
     * @throws \Context\Spin\TimeoutException
     */
    public function pimAiIsActivated(): void
    {
        $this->checkPimAiConfigurationIsSaved();
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
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     * @throws \Context\Spin\TimeoutException
     */
    private function configureValidToken(): void
    {
        $this->getNavigationContext()->iAmOnThePage('PIM.ai configuration');

        $this->spin(function (): bool {
            if (null === $this->getCurrentPage()->find('css', '.token-field')) {
                return false;
            }

            $this->getCurrentPage()->fillField('token', 'valid-token');

            return true;
        }, 'Impossible to fill the "token" field.');

        $this->getCurrentPage()->pressButton('Activate');
    }

    /**
     * @throws \Context\Spin\TimeoutException
     */
    private function checkPimAiConfigurationIsSaved(): void
    {
        $this->spin(function (): bool {
            $configuration = $this->configurationRepository->find();

            if (!$configuration instanceof Configuration) {
                return false;
            }

            return true;
        }, 'There is no PIM.ai configuration saved.');
    }

    /**
     * @throws \Context\Spin\TimeoutException
     */
    private function checkActivationButtonIsGreen()
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
        }, 'Activation button does not indicate that the connection to PIM.ai is active.');
    }
}
