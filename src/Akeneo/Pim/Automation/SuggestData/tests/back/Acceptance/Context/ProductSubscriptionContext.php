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

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Service\SubscribeProduct;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
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

    /**
     * @param InMemoryProductRepository             $productRepository
     * @param InMemoryProductSubscriptionRepository $productSubscriptionRepository
     * @param SubscribeProduct                      $subscribeProduct
     * @param DataFixturesContext                   $dataFixturesContext
     * @param SubscriptionFake                      $subscriptionApi
     */
    public function __construct(
        InMemoryProductRepository $productRepository,
        InMemoryProductSubscriptionRepository $productSubscriptionRepository,
        SubscribeProduct $subscribeProduct,
        DataFixturesContext $dataFixturesContext,
        SubscriptionFake $subscriptionApi
    ) {
        $this->productRepository = $productRepository;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->subscribeProduct = $subscribeProduct;
        $this->dataFixturesContext = $dataFixturesContext;
        $this->subscriptionApi = $subscriptionApi;
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
     * @Given the following product subscribed to pim.ai:
     *
     * @param TableNode $table
     */
    public function theFollowingProductSubscribedToPimAi(TableNode $table): void
    {
        $this->dataFixturesContext->theFollowingProduct($table);

        $productDefinition = $table->getColumnsHash()[0];
        $this->subscribeProductToPimAi($productDefinition['identifier'], true);
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
        $subscriptionStatus = $this->productSubscriptionRepository->getSubscriptionStatusForProductId(
            $product->getId()
        );

        Assert::isArray($subscriptionStatus);
        Assert::keyExists($subscriptionStatus, 'subscription_id');
        if (true === $not) {
            Assert::isEmpty($subscriptionStatus['subscription_id']);
        } else {
            Assert::notEmpty($subscriptionStatus['subscription_id']);
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
