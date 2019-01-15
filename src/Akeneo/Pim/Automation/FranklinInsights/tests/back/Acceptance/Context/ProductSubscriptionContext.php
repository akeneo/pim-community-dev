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

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\SubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\SubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Memory\InMemoryProductSubscriptionRepository;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Product\ProductRemoveSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
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

    /** @var ProductRemoveSubscriber */
    private $productRemovalSubscriber;

    /**
     * @param InMemoryProductRepository $productRepository
     * @param InMemoryProductSubscriptionRepository $productSubscriptionRepository
     * @param SubscribeProductHandler $subscribeProductHandler
     * @param DataFixturesContext $dataFixturesContext
     * @param UnsubscribeProductHandler $unsubscribeProductHandler
     * @param ProductRemoveSubscriber $productRemovalSubscriber
     */
    public function __construct(
        InMemoryProductRepository $productRepository,
        InMemoryProductSubscriptionRepository $productSubscriptionRepository,
        SubscribeProductHandler $subscribeProductHandler,
        DataFixturesContext $dataFixturesContext,
        UnsubscribeProductHandler $unsubscribeProductHandler,
        ProductRemoveSubscriber $productRemovalSubscriber
    ) {
        $this->productRepository = $productRepository;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->subscribeProductHandler = $subscribeProductHandler;
        $this->dataFixturesContext = $dataFixturesContext;
        $this->unsubscribeProductHandler = $unsubscribeProductHandler;
        $this->productRemovalSubscriber = $productRemovalSubscriber;
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
        } catch (\Exception $e) {
            ExceptionContext::setThrownException($e);
        }
    }

    /**
     * @When I unsubscribe the product :identifier
     *
     * @param string $identifier
     */
    public function iUnsubscribeTheProduct(string $identifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        Assert::isInstanceOf($product, ProductInterface::class);

        try {
            $command = new UnsubscribeProductCommand($product->getId());
            $this->unsubscribeProductHandler->handle($command);
        } catch (\Exception $e) {
            ExceptionContext::setThrownException($e);
        }
    }

    /**
     * Here, we are directly calling the subscriber. We should instead just
     * dispatch the post remove event, and let the subscriber stack do its job.
     * However, this is not possible for now, as Elasticsearch is also plugged to
     * this event (to remove the product from its index), and it is absolutely
     * not handled in our acceptance tests stack for now.
     *
     * @When I delete the product :identifier
     *
     * @param string $identifier
     */
    public function iDeleteTheProduct(string $identifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        Assert::isInstanceOf($product, ProductInterface::class);

        $this->productRemovalSubscriber->onPostRemove(new RemoveEvent($product, $product->getId()));
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
     * @Then the product ":identifier" should be subscribed
     *
     * @param string $identifier
     */
    public function theProductShouldBeSubscribed(string $identifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        $productSubscription = $this->productSubscriptionRepository->findOneByProductId($product->getId());
        Assert::isInstanceOf($productSubscription, ProductSubscription::class);
    }

    /**
     * @Then the product ":identifier" should not be subscribed
     *
     * @param string $identifier
     */
    public function theProductShouldNotBeSubscribed(string $identifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        $productSubscription = $this->productSubscriptionRepository->findOneByProductId($product->getId());
        Assert::null($productSubscription);
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
     * @Then a family required message should be sent
     */
    public function aFamilyRequiredMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::isInstanceOf($thrownException, ProductSubscriptionException::class);
        Assert::eq(
            ProductSubscriptionException::familyRequired()->getMessage(),
            $thrownException->getMessage()
        );
    }

    /**
     * @Then an invalid values message should be sent
     */
    public function anInvalidValuesMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::isInstanceOf($thrownException, ProductSubscriptionException::class);
        Assert::eq(
            ProductSubscriptionException::invalidMappedValues()->getMessage(),
            $thrownException->getMessage()
        );
    }

    /**
     * @Then an already subscribed message should be sent
     */
    public function anAlreadySubscribedMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::isInstanceOf($thrownException, ProductSubscriptionException::class);
        Assert::eq(
            ProductSubscriptionException::alreadySubscribedProduct()->getMessage(),
            $thrownException->getMessage()
        );
    }

    /**
     * @Then an invalid MPN and Brand message should be sent
     */
    public function anInvalidMpnAndBrandMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::isInstanceOf($thrownException, ProductSubscriptionException::class);
        Assert::eq(
            ProductSubscriptionException::invalidMappedValues()->getMessage(),
            $thrownException->getMessage()
        );
    }

    /**
     * @Then an invalid subscription message should be sent
     */
    public function anInvalidSubscriptionMessageShouldBeSent(): void
    {
        Assert::isInstanceOf(ExceptionContext::getThrownException(), ProductSubscriptionException::class);
    }

    /**
     * @Then a product not subscribed message should be sent
     */
    public function aProductNotSubscribedMessageShouldBeSent(): void
    {
        Assert::isInstanceOf(ExceptionContext::getThrownException(), ProductNotSubscribedException::class);
    }

    /**
     * @Then a not enough credit message should be sent
     */
    public function aNotEnoughCreditMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::isInstanceOf($thrownException, ProductSubscriptionException::class);
        Assert::eq(
            $thrownException->getMessage(),
            ProductSubscriptionException::insufficientCredits()->getMessage()
        );
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
