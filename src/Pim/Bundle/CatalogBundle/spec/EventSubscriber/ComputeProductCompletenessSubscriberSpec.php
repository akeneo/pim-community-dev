<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\EventSubscriber\ComputeProductCompletenessSubscriber;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ComputeProductCompletenessSubscriberSpec extends ObjectBehavior
{
    function let(CompletenessManager $completenessManager)
    {
        $this->beConstructedWith($completenessManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeProductCompletenessSubscriber::class);
    }

    function it_is_a_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn(
            [StorageEvents::PRE_SAVE => 'computeProductCompleteness']
        );
    }

    function it_only_computes_completeness_for_product(
        $completenessManager,
        GenericEvent $event,
        FamilyInterface $family
    ) {
        $event->getSubject()->willReturn($family);

        $completenessManager->schedule($family)->shouldNotBeCalled();
        $completenessManager->generateMissingForProduct($family)->shouldNotBeCalled();

        $this->computeProductCompleteness($event)->shouldReturn(null);
    }

    function it_computes_completeness(
        $completenessManager,
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);

        $completenessManager->schedule($product)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($product)->shouldBeCalled();

        $this->computeProductCompleteness($event)->shouldReturn(null);
    }
}
