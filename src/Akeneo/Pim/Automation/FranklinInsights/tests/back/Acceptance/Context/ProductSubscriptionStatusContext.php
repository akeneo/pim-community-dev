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

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Context;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionStatus;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProductSubscriptionStatusContext implements Context
{
    /** @var ProductSubscriptionStatus|null */
    private $productSubscriptionStatus;

    /** @var GetProductSubscriptionStatusHandler */
    private $getProductSubscriptionStatusHandler;

    /** @var InMemoryProductRepository */
    private $productRepository;

    /**
     * @param InMemoryProductRepository $productRepository
     * @param GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler
     */
    public function __construct(
        InMemoryProductRepository $productRepository,
        GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler
    ) {
        $this->getProductSubscriptionStatusHandler = $getProductSubscriptionStatusHandler;
        $this->productRepository = $productRepository;
    }

    /**
     * @param string $identifier
     *
     * @When I retrieve the product subscription status of the product ":identifier"
     */
    public function iRetrieveTheProductSubscriptionStatusOfTheProduct($identifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        if (null === $product) {
            throw new \InvalidArgumentException(sprintf('No product with identifier "%s" found.', $identifier));
        }

        $query = new GetProductSubscriptionStatusQuery($product->getId());
        $this->productSubscriptionStatus = $this->getProductSubscriptionStatusHandler->handle($query);
    }

    /**
     * @Then the product subscription status has no family
     */
    public function theProductSubscriptionStatusHasNoFamily(): void
    {
        if (null === $this->productSubscriptionStatus) {
            throw new \BadMethodCallException('No product subscription status to check.');
        }

        Assert::assertFalse($this->productSubscriptionStatus->hasFamily());
    }

    /**
     * @Then the product subscription status has family
     */
    public function theProductSubscriptionStatusHasFamily(): void
    {
        if (null === $this->productSubscriptionStatus) {
            throw new \BadMethodCallException('No product subscription status to check.');
        }

        Assert::assertTrue($this->productSubscriptionStatus->hasFamily());
    }

    /**
     * @Then the product subscription status indicates that mapping is not filled
     */
    public function theProductSubscriptionStatusIndicatesThatMappingIsNotFilled(): void
    {
        if (null === $this->productSubscriptionStatus) {
            throw new \BadMethodCallException('No product subscription status to check.');
        }

        Assert::assertFalse($this->productSubscriptionStatus->isMappingFilled());
    }

    /**
     * @Then the product subscription status indicates that mapping is filled
     */
    public function theProductSubscriptionStatusIndicatesThatMappingIsFilled(): void
    {
        if (null === $this->productSubscriptionStatus) {
            throw new \BadMethodCallException('No product subscription status to check.');
        }

        Assert::assertTrue($this->productSubscriptionStatus->isMappingFilled());
    }

    /**
     * @Then the product subscription status has no subscribed product
     */
    public function theProductSubscriptionStatusHasNoSubscribedProduct(): void
    {
        if (null === $this->productSubscriptionStatus) {
            throw new \BadMethodCallException('No product subscription status to check.');
        }

        Assert::assertFalse($this->productSubscriptionStatus->isSubscribed());
    }

    /**
     * @Then the product subscription status has subscribed product
     */
    public function theProductSubscriptionStatusHasSubscribedProduct(): void
    {
        if (null === $this->productSubscriptionStatus) {
            throw new \BadMethodCallException('No product subscription status to check.');
        }

        Assert::assertTrue($this->productSubscriptionStatus->isSubscribed());
    }
}
