<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Service\ResubscribeProductsInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Query\Product\SelectProductIdentifierValuesQueryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Subscriber\Product\ProductUpdateSubscriber;
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
        ResubscribeProductsInterface $resubscribeProducts
    ): void {
        $this->beConstructedWith($subscriptionRepository, $selectProductIdentifierValuesQuery, $resubscribeProducts);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_is_a_product_update_subscriber(): void
    {
        $this->shouldHaveType(ProductUpdateSubscriber::class);
    }

    public function it_subscribes_to_post_save_all_event(): void
    {
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE_ALL);
    }

    public function it_only_processes_products(
        $subscriptionRepository,
        $selectProductIdentifierValuesQuery,
        $resubscribeProducts
    ): void {
        $subscriptionRepository->findOneByProductId(Argument::any())->shouldNotBeCalled();
        $selectProductIdentifierValuesQuery->execute(Argument::any())->shouldNotBeCalled();

        $resubscribeProducts->process(Argument::any())->shouldNotBeCalled();

        $this->computeImpactedSubscriptions(new GenericEvent([new \stdClass(), new Attribute()]));
    }

    public function it_does_not_process_unsubscribed_products(
        $subscriptionRepository,
        $selectProductIdentifierValuesQuery,
        $resubscribeProducts,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(42);
        $subscriptionRepository->findOneByProductId(42)->willReturn(null);
        $selectProductIdentifierValuesQuery->execute(42)->shouldNotBeCalled();

        $resubscribeProducts->process(Argument::any())->shouldNotBeCalled();

        $this->computeImpactedSubscriptions(new GenericEvent([$product->getWrappedObject()]));
    }

    public function it_does_not_process_products_with_unchanged_identifier_values(
        $subscriptionRepository,
        $selectProductIdentifierValuesQuery,
        $resubscribeProducts,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(42);
        $subscriptionRepository->findOneByProductId(42)->willReturn(
            new ProductSubscription(42, 'abc-123', ['asin' => 'ABC123'])
        );
        $selectProductIdentifierValuesQuery->execute(42)->willReturn(
            new ProductIdentifierValues(
                ['asin' => 'ABC123', 'upc' => '123456']
            )
        );

        $resubscribeProducts->process(Argument::any())->shouldNotBeCalled();

        $this->computeImpactedSubscriptions(new GenericEvent([$product->getWrappedObject()]));
    }

    public function it_processes_products_with_changed_identifier_values(
        $subscriptionRepository,
        $selectProductIdentifierValuesQuery,
        $resubscribeProducts,
        ProductInterface $product1,
        ProductInterface $product2
    ): void {
        $product1->getId()->willReturn(42);
        $subscriptionRepository->findOneByProductId(42)->willReturn(
            new ProductSubscription(42, 'abc-123', ['asin' => 'ABC123', 'upc' => '987654321'])
        );
        $selectProductIdentifierValuesQuery->execute(42)->willReturn(
            new ProductIdentifierValues(
                ['mpn' => 'Akeneo-PIM', 'brand' => 'Akeneo']
            )
        );

        $product2->getId()->willReturn(44);
        $subscriptionRepository->findOneByProductId(44)->willReturn(
            new ProductSubscription(42, 'abc-123', ['asin' => 'DEF987'])
        );
        $selectProductIdentifierValuesQuery->execute(42)->willReturn(
            new ProductIdentifierValues(
                ['asin' => 'DEF987']
            )
        );

        $resubscribeProducts->process([42])->shouldBeCalled();

        $this->computeImpactedSubscriptions(
            new GenericEvent([$product1->getWrappedObject(), $product2->getWrappedObject()])
        );
    }
}
