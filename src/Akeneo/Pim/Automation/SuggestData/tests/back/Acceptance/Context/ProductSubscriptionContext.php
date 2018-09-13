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

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Service\SubscribeProduct;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\SubscriptionFake;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Memory\InMemoryProductSubscriptionRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionContext implements Context
{
    /** @var InMemoryProductRepository */
    private $productRepository;

    /** @var InMemoryProductSubscriptionRepository */
    private $productSubscriptionRepository;

    /** @var SubscribeProduct */
    private $subscribeProduct;

    /** @var DataFixturesContext */
    private $dataFixturesContext;

    /** @var SubscriptionFake */
    private $subscriptionApi;

    /** @var UnsubscribeProductHandler */
    private $unsubscribeProductHandler;

    /**
     * @param InMemoryProductRepository $productRepository
     * @param InMemoryProductSubscriptionRepository $productSubscriptionRepository
     * @param SubscribeProduct $subscribeProduct
     * @param DataFixturesContext $dataFixturesContext
     * @param SubscriptionFake $subscriptionApi
     * @param UnsubscribeProductHandler $unsubscribeProductHandler
     */
    public function __construct(
        InMemoryProductRepository $productRepository,
        InMemoryProductSubscriptionRepository $productSubscriptionRepository,
        SubscribeProduct $subscribeProduct,
        DataFixturesContext $dataFixturesContext,
        SubscriptionFake $subscriptionApi,
        UnsubscribeProductHandler $unsubscribeProductHandler
    ) {
        $this->productRepository = $productRepository;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->subscribeProduct = $subscribeProduct;
        $this->dataFixturesContext = $dataFixturesContext;
        $this->subscriptionApi = $subscriptionApi;
        $this->unsubscribeProductHandler = $unsubscribeProductHandler;
    }

    /**
     * @When I subscribe the product :identifier to PIM.ai
     *
     * @param string $identifier
     */
    public function iSubscribeTheProductToPimAi(string $identifier): void
    {
        $this->subscribeProductToPimAi($identifier, false);
    }

    /**
     * @When I unsubscribe the product :identifier
     *
     * @param string $identifier
     * @throws ProductSubscriptionException
     */
    public function iUnsubscribeTheProduct(string $identifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        Assert::isInstanceOf($product, ProductInterface::class);

        try {
            $command = new UnsubscribeProductCommand($product->getId());
            $this->unsubscribeProductHandler->handle($command);
        } catch (ProductSubscriptionException $e) {
        }
    }

    /**
     * @Given the following product subscribed to pim.ai:
     *
     * @param TableNode $table
     */
    public function theFollowingProductSubscribedToPimAi(TableNode $table): void
    {
        $this->dataFixturesContext->theFollowingProduct($table);

        foreach ($table->getHash() as $productRow) {
            $this->subscribeProductToPimAi($productRow['identifier'], true);
        }
    }

    /**
     * @Then /^the product "([^"]*)" should(| not) be subscribed$/
     *
     * @param string $identifier
     * @param bool $not
     */
    public function theProductShouldBeSubscribed(string $identifier, bool $not): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);

        $productSubscription = $this->productSubscriptionRepository->findOneByProductId($product->getId());

        if ($not) {
            Assert::null($productSubscription);
        } else {
            Assert::isInstanceOf($productSubscription, ProductSubscription::class);
        }
    }

    /**
     * @Given the PIM.ai token is expired
     */
    public function theTokenIsExpired(): void
    {
        $this->subscriptionApi->expireToken();
    }

    /**
     * @Given there are no more credits on my PIM.ai account
     */
    public function thereAreNoMoreCreditsOnMyAccount()
    {
        $this->subscriptionApi->disableCredit();
    }

    /**
     * @Then /^([0-9]*) suggested data should have been added$/
     *
     * @param mixed $count (could be
     */
    public function suggestedDataHaveBeenAdded(int $count)
    {
        $expectedNumber = (int) $count;

        $pendingSubscriptions = $this->productSubscriptionRepository->findPendingSubscriptions();
        Assert::count($pendingSubscriptions, $expectedNumber);
    }

    /**
     * @param string $identifier
     * @param bool $throwExceptions
     */
    private function subscribeProductToPimAi(string $identifier, bool $throwExceptions = false): void
    {
        $product = $this->findProduct($identifier);
        try {
            $this->subscribeProduct->subscribe($product->getId());
        } catch (ProductSubscriptionException $e) {
            if (true === $throwExceptions) {
                throw $e;
            }
        }
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface
     */
    private function findProduct(string $identifier): ProductInterface
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        if (null === $product) {
            throw new \InvalidArgumentException(
                sprintf('Product "%s" does not exist', $identifier)
            );
        }

        return $product;
    }
}
