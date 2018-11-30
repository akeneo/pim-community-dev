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

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Repository\Memory\InMemoryProductSubscriptionRepository;
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

    /** @var SubscribeProductHandler */
    private $subscribeProductHandler;

    /** @var DataFixturesContext */
    private $dataFixturesContext;

    /** @var UnsubscribeProductHandler */
    private $unsubscribeProductHandler;

    /** @var null|\Exception */
    private $thrownException;

    /**
     * @param InMemoryProductRepository $productRepository
     * @param InMemoryProductSubscriptionRepository $productSubscriptionRepository
     * @param SubscribeProductHandler $subscribeProductHandler
     * @param DataFixturesContext $dataFixturesContext
     * @param UnsubscribeProductHandler $unsubscribeProductHandler
     */
    public function __construct(
        InMemoryProductRepository $productRepository,
        InMemoryProductSubscriptionRepository $productSubscriptionRepository,
        SubscribeProductHandler $subscribeProductHandler,
        DataFixturesContext $dataFixturesContext,
        UnsubscribeProductHandler $unsubscribeProductHandler
    ) {
        $this->productRepository = $productRepository;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->subscribeProductHandler = $subscribeProductHandler;
        $this->dataFixturesContext = $dataFixturesContext;
        $this->unsubscribeProductHandler = $unsubscribeProductHandler;
    }

    /**
     * @When I subscribe the product :identifier to Franklin
     *
     * @param string $identifier
     */
    public function iSubscribeTheProductToFranklin(string $identifier): void
    {
        $product = $this->findProduct($identifier);

        try {
            $command = new SubscribeProductCommand($product->getId());
            $this->subscribeProductHandler->handle($command);
        } catch (ProductSubscriptionException $e) {
            $this->thrownException = $e;
        }
    }

    /**
     * @When I unsubscribe the product :identifier
     *
     * @param string $identifier
     *
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
            $this->thrownException = $e;
        } catch (ProductNotSubscribedException $e) {
            $this->thrownException = $e;
        }
    }

    /**
     * @Given the following product subscribed to Franklin:
     *
     * @param TableNode $table
     */
    public function theFollowingProductSubscribedToFranklin(TableNode $table): void
    {
        $this->dataFixturesContext->theFollowingProduct($table);

        foreach ($table->getHash() as $productRow) {
            $product = $this->findProduct($productRow['identifier']);

            $command = new SubscribeProductCommand($product->getId());
            $this->subscribeProductHandler->handle($command);
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
     * @Then /^([0-9]*) suggested data should have been added$/
     *
     * @param int $count
     */
    public function suggestedDataHaveBeenAdded(int $count): void
    {
        $pendingSubscriptions = $this->productSubscriptionRepository->findPendingSubscriptions($count, null);
        Assert::count($pendingSubscriptions, $count);
    }

    /**
     * @Then the suggested data for the subscription of product :identifier should be empty
     *
     * @param string $identifier
     */
    public function theSuggestedDataForTheSubscriptionOfProductShouldBeEmpty(string $identifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        $subscription = $this->productSubscriptionRepository->findOneByProductId($product->getId());

        Assert::true($subscription->getSuggestedData()->isEmpty());
    }

    /**
     * @Then an invalid family message should be sent
     */
    public function anInvalidFamilyMessageShouldBeSent(): void
    {
        Assert::isInstanceOf($this->thrownException, ProductSubscriptionException::class);
        Assert::eq(
            ProductSubscriptionException::familyRequired()->getMessage(),
            $this->thrownException->getMessage()
        );
    }

    /**
     * @Then an invalid values message should be sent
     */
    public function anInvalidValuesMessageShouldBeSent(): void
    {
        Assert::isInstanceOf($this->thrownException, ProductSubscriptionException::class);
        Assert::eq(
            ProductSubscriptionException::invalidMappedValues()->getMessage(),
            $this->thrownException->getMessage()
        );
    }

    /**
     * @Then an already subscribed message should be sent
     */
    public function anAlreadySubscribedMessageShouldBeSent(): void
    {
        Assert::isInstanceOf($this->thrownException, ProductSubscriptionException::class);
    }

    /**
     * @Then a not enough credit message should be sent
     */
    public function aNotEnoughCreditMessageShouldBeSent(): void
    {
        Assert::isInstanceOf($this->thrownException, ProductSubscriptionException::class);
        Assert::eq(
            ProductSubscriptionException::insufficientCredits()->getMessage(),
            $this->thrownException->getMessage()
        );
    }

    /**
     * @Then an invalid MPN and Brand message should be sent
     */
    public function anInvalidMpnAndBrandMessageShouldBeSent(): void
    {
        Assert::isInstanceOf($this->thrownException, ProductSubscriptionException::class);
        Assert::eq(
            ProductSubscriptionException::invalidMappedValues()->getMessage(),
            $this->thrownException->getMessage()
        );
    }

    /**
     * @Then a Franklin subscription error message should be sent
     */
    public function aFranklinSubscriptionErrorMessageShouldBeSent(): void
    {
        Assert::isInstanceOf($this->thrownException, ProductSubscriptionException::class);
        Assert::eq(
            ProductSubscriptionException::dataProviderError()->getMessage(),
            $this->thrownException->getMessage()
        );
    }

    /**
     * @Then an invalid subscription message should be sent
     */
    public function anInvalidSubscriptionMessageShouldBeSent(): void
    {
        Assert::isInstanceOf($this->thrownException, ProductSubscriptionException::class);
    }

    /**
     * @Then a token invalid message for subscription should be sent
     */
    public function aTokenInvalidMessageForSubscriptionShouldBeSent(): void
    {
        Assert::isInstanceOf($this->thrownException, ProductSubscriptionException::class);
        Assert::eq(
            ProductSubscriptionException::invalidToken()->getMessage(),
            $this->thrownException->getMessage()
        );
    }

    /**
     * @Then a product not subscribed message should be sent
     */
    public function aProductNotSubscribedMessageShouldBeSent(): void
    {
        Assert::isInstanceOf($this->thrownException, ProductNotSubscribedException::class);
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
