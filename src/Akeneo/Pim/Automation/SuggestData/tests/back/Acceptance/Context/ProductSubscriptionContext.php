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
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Memory\InMemoryProductSubscriptionRepository;
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

    /**
     * @param InMemoryProductRepository $productRepository
     * @param InMemoryProductSubscriptionRepository $productSubscriptionRepository
     * @param SubscribeProduct $subscribeProduct
     */
    public function __construct(
        InMemoryProductRepository $productRepository,
        InMemoryProductSubscriptionRepository $productSubscriptionRepository,
        SubscribeProduct $subscribeProduct,
        DataFixturesContext $dataFixturesContext
    ) {
        $this->productRepository = $productRepository;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->subscribeProduct = $subscribeProduct;
        $this->dataFixturesContext = $dataFixturesContext;
    }

    /**
     * @When I subscribe the product :identifier to PIM.ai
     */
    public function iSubscribeTheProductToPimAi(string $identifier)
    {
        $this->subscribeProductToPimAi($identifier);
    }

    /**
     * @Given the following product subscribed to pim.ai:
     */
    public function theFollowingProductSubscribedToPimAi(TableNode $table)
    {
        $this->dataFixturesContext->theFollowingProduct($table);

        $productDefinition = $table->getColumnsHash()[0];
        $this->subscribeProductToPimAi($productDefinition['identifier']);
    }

    /**
     * @Then the product :identifier should be subscribed
     */
    public function theProductShouldBeSubscribed(string $identifier)
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);

        Assert::true($this->productSubscriptionRepository->existsForProductId($product->getId()));
    }

    private function subscribeProductToPimAi(string $identifier)
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        if (null === $product) {
            throw new \InvalidArgumentException(
                sprintf('Product "%s" does not exist', $identifier)
            );
        }

        $this->subscribeProduct->subscribe($product->getId());
    }
}
