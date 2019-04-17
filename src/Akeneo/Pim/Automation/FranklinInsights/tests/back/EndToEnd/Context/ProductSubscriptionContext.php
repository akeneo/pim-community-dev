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

namespace Akeneo\Test\Pim\Automation\FranklinInsights\EndToEnd\Context;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Context\Traits\ClosestTrait;
use Pim\Behat\Context\PimContext;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ProductSubscriptionContext extends PimContext
{
    use ClosestTrait;
    use SpinCapableTrait;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /**
     * @param string $mainContextClass
     * @param ProductRepositoryInterface $productRepository
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     */
    public function __construct(
        string $mainContextClass,
        ProductRepositoryInterface $productRepository,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository
    ) {
        parent::__construct($mainContextClass);

        $this->productRepository = $productRepository;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
    }

    /**
     * @When I subscribe the product :identifier to Franklin
     *
     * @param string $identifier
     *
     * @throws TimeoutException
     */
    public function iSubscribeTheProductToFranklin(string $identifier): void
    {
        $this->loginAsAdmin();
        $this->subscribeProductToFranklin($identifier);
    }

    /**
     * @When I unsubscribe the product :identifier
     *
     * @param string $identifier
     *
     * @throws TimeoutException
     */
    public function iUnsubscribeTheProductToFranklin(string $identifier): void
    {
        $this->loginAsAdmin();
        $this->unsubscribeProductFromFranklin($identifier);
    }

    /**
     * @When /I bulk subscribe the products (.*) to Franklin/
     */
    public function iSubscribeTheProductsToFranklin(string $identifiers): void
    {
        $this->getNavigationContext()->iAmLoggedInAs('admin', 'admin');
        $this->getNavigationContext()->iAmOnTheGrid('products');

        $identifiers = $this->getMainContext()->listToArray($identifiers);

        foreach ($identifiers as $entity) {
            $this->getCurrentPage()->selectRow($entity, true);
        }

        $this->getMainContext()->getSubcontext('webUser')->iPressTheButton('Bulk actions');
        $this->getMainContext()->getSubcontext('webUser')->iChooseTheOperation('Franklin Insights Subscriptions');
        $this->getMainContext()->getSubcontext('webUser')->iConfirmTheMassEdit();
        $this->getMainContext()->getSubcontext('webUser')->iWaitForTheJobToFinish(JobInstanceNames::SUBSCRIBE_PRODUCTS);
    }

    /**
     * @Then /the products (.*) should be subscribed/
     *
     * @param string $identifiers
     */
    public function theProductsShouldBeSubscribed(string $identifiers): void
    {
        $identifiers = $this->getMainContext()->listToArray($identifiers);

        foreach ($identifiers as $identifier) {
            $this->checkSubscriptionIsSaved($identifier);
        }
    }

    /**
     * @Then the product :identifier should be subscribed
     *
     * @param string $identifier
     *
     * @throws TimeoutException
     */
    public function theProductShouldBeSubscribed(string $identifier): void
    {
        $this->checkSubscriptionIsSaved($identifier);
        $this->checkStatusIsEnable();
    }

    /**
     * @Then the product :identifier should not be subscribed
     *
     * @param string $identifier
     *
     * @throws TimeoutException
     */
    public function theProductShouldNotBeSubscribed(string $identifier): void
    {
        $this->checkSubscriptionIsNotSaved($identifier);
        $this->checkStatusIsDisabled();
    }

    private function loginAsAdmin(): void
    {
        $this->getNavigationContext()->iAmLoggedInAs('admin', 'admin');
    }

    /**
     * @param string $identifier
     *
     * @throws TimeoutException
     */
    private function subscribeProductToFranklin(string $identifier): void
    {
        $dropdown = $this->getActivationDropDown($identifier);

        $this->spin(function () use ($identifier, $dropdown): bool {
            $dropdown->click();
            $button = $dropdown->find('css', '.franklin-subscription-enabled');
            if (null === $button) {
                return false;
            }
            $button->click();

            return true;
        }, sprintf('Cannot subscribe product "%s" to Franklin.', $identifier));
    }

    /**
     * @param string $identifier
     *
     * @throws TimeoutException
     */
    private function unsubscribeProductFromFranklin(string $identifier): void
    {
        $dropdown = $this->getActivationDropDown($identifier);

        $this->spin(function () use ($identifier, $dropdown): bool {
            $dropdown->click();
            $button = $dropdown->find('css', '.franklin-subscription-disabled');
            if (null === $button) {
                return false;
            }
            $button->click();

            return true;
        }, sprintf('Cannot unsubscribe product "%s" from Franklin.', $identifier));
    }

    /**
     * @param string $identifier
     *
     * @throws TimeoutException
     *
     * @return NodeElement
     */
    private function getActivationDropDown(string $identifier): NodeElement
    {
        $this->getNavigationContext()->iAmOnTheEntityEditPage($identifier, 'product');

        return $this->spin(function () use ($identifier): ?NodeElement {
            $nodeElement = $this->getCurrentPage()->find('css', '.ask-franklin-subscription-status');
            if (null === $nodeElement) {
                return null;
            }

            $dropdown = $this->getClosest($nodeElement, 'AknDropdown');
            if (null === $dropdown) {
                return null;
            }

            return $dropdown;
        }, sprintf('Cannot find Franklin subscription drop-down for product "%s".', $identifier));
    }

    /**
     * @param string $identifier
     *
     * @throws TimeoutException
     */
    private function checkSubscriptionIsSaved(string $identifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);

        $this->spin(function () use ($product): bool {
            $productSubscription = $this->productSubscriptionRepository->findOneByProductId(new ProductId($product->getId()));

            return $productSubscription instanceof ProductSubscription;
        }, sprintf('Cannot find any subscription for product "%s".', $product->getIdentifier()));
    }

    /**
     * @param string $identifier
     *
     * @throws TimeoutException
     */
    private function checkSubscriptionIsNotSaved(string $identifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);

        $this->spin(function () use ($product): bool {
            $productSubscription = $this->productSubscriptionRepository->findOneByProductId(new ProductId($product->getId()));

            return null === $productSubscription;
        }, sprintf('Found a subscription for product "%s" when there should be none.', $product->getIdentifier()));
    }

    /**
     * @throws TimeoutException
     */
    private function checkStatusIsEnable(): void
    {
        $this->spin(function (): bool {
            if (null === $status = $this->getCurrentPage()->find('css', '.ask-franklin-subscription-status')) {
                return false;
            }

            return 'Enabled' === $status->getText();
        }, 'The subscription status is not "Enabled".');
    }

    /**
     * @throws TimeoutException
     */
    private function checkStatusIsDisabled(): void
    {
        $this->spin(function (): bool {
            if (null === $status = $this->getCurrentPage()->find('css', '.ask-franklin-subscription-status')) {
                return false;
            }

            return 'Disabled' === $status->getText();
        }, 'The subscription status is not "Disabled".');
    }
}
