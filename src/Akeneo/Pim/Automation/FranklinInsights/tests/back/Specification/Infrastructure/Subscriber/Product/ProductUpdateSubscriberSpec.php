<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Service\ResubscribeProductsInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValuesCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductIdentifierValuesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Product\ProductUpdateSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductUpdateSubscriberSpec extends ObjectBehavior
{
    public function let(
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        SelectProductIdentifierValuesQueryInterface $selectProductIdentifierValuesQuery,
        ResubscribeProductsInterface $resubscribeProducts,
        GetConnectionStatusHandler $connectionStatusHandler
    ): void {
        $this->beConstructedWith(
            $subscriptionRepository,
            $selectProductIdentifierValuesQuery,
            $resubscribeProducts,
            $connectionStatusHandler
        );

        $connectionStatus = new ConnectionStatus(true, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_is_a_product_update_subscriber(): void
    {
        $this->shouldHaveType(ProductUpdateSubscriber::class);
    }

    public function it_subscribes_to_post_save_and_post_save_all_events(): void
    {
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE);
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE_ALL);
    }

    public function it_does_nothing_on_non_unitary_post_save(
        $subscriptionRepository,
        $selectProductIdentifierValuesQuery,
        $resubscribeProducts,
        ProductInterface $product
    ): void {
        $subscriptionRepository->findByProductIds(Argument::any())->shouldNotBeCalled();
        $selectProductIdentifierValuesQuery->execute(Argument::any())->shouldNotBeCalled();
        $resubscribeProducts->process(Argument::any())->shouldNotBeCalled();

        $this->onPostSave(new GenericEvent($product->getWrappedObject(), ['unitary' => false]));
    }

    public function it_only_processes_products(
        $subscriptionRepository,
        $selectProductIdentifierValuesQuery,
        $resubscribeProducts
    ): void {
        $selectProductIdentifierValuesQuery->execute(Argument::any())->shouldNotBeCalled();
        $subscriptionRepository->findByProductIds(Argument::any())->shouldNotBeCalled();
        $resubscribeProducts->process(Argument::any())->shouldNotBeCalled();

        $this->onPostSave(new GenericEvent(new \stdClass(), ['unitary' => true]));
        $this->onPostSaveAll(new GenericEvent([new \stdClass(), new Attribute()]));
    }

    public function it_does_not_process_an_unsubscribed_product(
        $subscriptionRepository,
        $selectProductIdentifierValuesQuery,
        $resubscribeProducts,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(42);
        $subscriptionRepository->findByProductIds([42])->willReturn([]);

        $selectProductIdentifierValuesQuery->execute(Argument::any())->shouldNotBeCalled();
        $resubscribeProducts->process(Argument::any())->shouldNotBeCalled();

        $this->onPostSave(new GenericEvent($product->getWrappedObject(), ['unitary' => true]));
    }

    public function it_does_not_process_products_if_franklin_insights_is_not_activated(
        $subscriptionRepository,
        $selectProductIdentifierValuesQuery,
        $resubscribeProducts,
        $connectionStatusHandler,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(42);

        $connectionStatus = new ConnectionStatus(false, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);

        $subscriptionRepository->findByProductIds(Argument::any())->shouldNotBeCalled();
        $selectProductIdentifierValuesQuery->execute(Argument::any())->shouldNotBeCalled();
        $resubscribeProducts->process(Argument::any())->shouldNotBeCalled();

        $this->onPostSave(new GenericEvent($product->getWrappedObject(), ['unitary' => true]));
        $this->onPostSaveAll(new GenericEvent([$product->getWrappedObject()]));
    }

    public function it_does_not_process_unsubscribed_products(
        $subscriptionRepository,
        $selectProductIdentifierValuesQuery,
        $resubscribeProducts,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(42);
        $subscriptionRepository->findByProductIds([42])->willReturn([]);

        $selectProductIdentifierValuesQuery->execute(Argument::any())->shouldNotBeCalled();
        $resubscribeProducts->process(Argument::any())->shouldNotBeCalled();

        $this->onPostSaveAll(new GenericEvent([$product->getWrappedObject()]));
    }

    public function it_does_not_process_products_with_unchanged_identifier_values(
        $subscriptionRepository,
        $selectProductIdentifierValuesQuery,
        $resubscribeProducts,
        ProductInterface $product
    ): void {
        $subscription = new ProductSubscription(42, new SubscriptionId('abc-123'), ['asin' => 'ABC123']);
        $product->getId()->willReturn(42);

        $identifierValuesCollection = new ProductIdentifierValuesCollection();
        $identifierValuesCollection->add(
            new ProductIdentifierValues(
                42,
                ['asin' => 'ABC123', 'upc' => '123456']
            )
        );

        $selectProductIdentifierValuesQuery->execute([42])->willReturn($identifierValuesCollection);
        $subscriptionRepository->findByProductIds([42])->willReturn([$subscription]);

        $resubscribeProducts->process(Argument::any())->shouldNotBeCalled();

        $this->onPostSave(new GenericEvent($product->getWrappedObject(), ['unitary' => true]));
        $this->onPostsaveAll(new GenericEvent([$product->getWrappedObject()]));
    }

    public function it_processes_a_product_with_changed_identifier_values(
        $subscriptionRepository,
        $selectProductIdentifierValuesQuery,
        $resubscribeProducts,
        ProductInterface $product
    ): void {
        $subscription = new ProductSubscription(42, new SubscriptionId('a-fake-id'), ['asin' => '123456']);
        $subscriptionRepository->findByProductIds([42])->willReturn([$subscription]);

        $product->getId()->willReturn(42);
        $identifierValuesCollection = new ProductIdentifierValuesCollection();
        $identifierValuesCollection->add(
            new ProductIdentifierValues(
                42,
                ['mpn' => 'Akeneo-PIM', 'brand' => 'Akeneo']
            )
        );
        $selectProductIdentifierValuesQuery->execute([42])->willReturn($identifierValuesCollection);

        $resubscribeProducts->process([42])->shouldBeCalled();

        $this->onPostSave(new GenericEvent($product->getWrappedObject(), ['unitary' => true]));
    }

    public function it_processes_products_with_changed_identifier_values(
        $subscriptionRepository,
        $selectProductIdentifierValuesQuery,
        $resubscribeProducts,
        ProductInterface $product1,
        ProductInterface $product2
    ): void {
        $product1->getId()->willReturn(42);
        $product2->getId()->willReturn(44);
        $subscriptionRepository->findByProductIds([42, 44])->willReturn(
            [
                new ProductSubscription(42, new SubscriptionId('abc-123'), ['asin' => 'ABC123', 'upc' => '987654321']),
                new ProductSubscription(44, new SubscriptionId('def-456'), ['asin' => 'DEF987']),
            ]
        );

        $identifierValuesCollection = new ProductIdentifierValuesCollection();
        $identifierValuesCollection->add(
            new ProductIdentifierValues(
                42,
                ['mpn' => 'Akeneo-PIM', 'brand' => 'Akeneo']
            ));
        $identifierValuesCollection->add(
            new ProductIdentifierValues(
                44,
                ['asin' => 'DEF987']
            )
        );
        $selectProductIdentifierValuesQuery->execute([42, 44])->willReturn($identifierValuesCollection);

        $resubscribeProducts->process([42])->shouldBeCalled();

        $this->onPostSaveAll(
            new GenericEvent([$product1->getWrappedObject(), $product2->getWrappedObject()])
        );
    }
}
