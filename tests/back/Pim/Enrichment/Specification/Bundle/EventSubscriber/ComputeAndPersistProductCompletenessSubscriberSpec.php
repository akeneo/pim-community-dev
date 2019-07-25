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
        $computeAndPersistProductCompletenesses->fromProductIdentifier('product')->shouldBeCalled();

        $product = new Product();
        $product->setIdentifier(ScalarValue::value('foo', 'product'));

        $this->computeProductCompleteness(new GenericEvent($product));
    }

    function it_does_nothing_for_anything_but_a_product(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses
    ) {
        $computeAndPersistProductCompletenesses->fromProductIdentifier(Argument::any())->shouldNotBeCalled();

        $this->computeProductCompleteness(new GenericEvent(new \stdClass()));
    }
}
