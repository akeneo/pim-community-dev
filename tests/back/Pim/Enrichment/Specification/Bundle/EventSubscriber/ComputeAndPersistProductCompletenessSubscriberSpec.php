<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ComputeAndPersistProductCompletenessSubscriber;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ComputeAndPersistProductCompletenessSubscriberSpec extends ObjectBehavior
{
    function let(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses
    ) {
        $this->beConstructedWith($computeAndPersistProductCompletenesses);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_is_a_compute_product_completeness_event_subscriber()
    {
        $this->shouldHaveType(ComputeAndPersistProductCompletenessSubscriber::class);
    }

    function it_computes_and_saves_completenesses_for_a_product(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses
    ) {
        $computeAndPersistProductCompletenesses->fromProductIdentifiers(['product'])->shouldBeCalled();

        $product = new Product();
        $product->setIdentifier(ScalarValue::value('foo', 'product'));

        $this->computeProductCompleteness(new GenericEvent($product));
    }

    function it_does_not_compute_if_it_is_not_unitary_for_save(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses
    ) {
        $computeAndPersistProductCompletenesses->fromProductIdentifiers(['product'])->shouldBeCalled();

        $product = new Product();
        $product->setIdentifier(ScalarValue::value('foo', 'product'));

        $this->computeProductCompleteness(new GenericEvent($product, ['unitary' => true]));
    }

    function it_does_nothing_for_anything_but_a_product(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses
    ) {
        $computeAndPersistProductCompletenesses->fromProductIdentifiers([Argument::any()])->shouldNotBeCalled();

        $this->computeProductCompleteness(new GenericEvent(new \stdClass()));
    }

    function it_computes_and_saves_completenesses_for_multiple_products(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses
    ) {
        $computeAndPersistProductCompletenesses->fromProductIdentifiers(['product_a', 'product_b'])->shouldBeCalled();

        $productA = new Product();
        $productA->setIdentifier(ScalarValue::value('foo', 'product_a'));
        $productB = new Product();
        $productB->setIdentifier(ScalarValue::value('bar', 'product_b'));

        $this->computeProductsCompleteness(new GenericEvent([$productA, $productB]));
    }
}
