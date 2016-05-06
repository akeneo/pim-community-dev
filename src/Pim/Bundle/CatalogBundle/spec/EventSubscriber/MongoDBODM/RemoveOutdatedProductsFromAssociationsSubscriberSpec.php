<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Akeneo\Component\Console\CommandLauncher;
use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\ProductEvents;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class RemoveOutdatedProductsFromAssociationsSubscriberSpec extends ObjectBehavior
{
    function let(CommandLauncher $launcher, $logFile)
    {
        $this->beConstructedWith($launcher, $logFile);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_some_product_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                StorageEvents::POST_REMOVE      => 'removeAssociatedProduct',
                ProductEvents::POST_MASS_REMOVE => 'removeAssociatedProducts',
            ]
        );
    }

    function it_removes_associated_product_in_background(
        $launcher,
        $logFile,
        RemoveEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);
        $event->getSubjectId()->willReturn(2);

        $launcher->executeBackground('pim:product:remove-from-associations 2', $logFile)->shouldBeCalled();

        $this->removeAssociatedProduct($event);
    }

    function it_removes_associated_products_on_many_products_in_background(
        $launcher,
        $logFile,
        RemoveEvent $event
    ) {
        $event->getSubject()->willReturn([1, 2, 3]);

        $launcher->executeBackground('pim:product:remove-from-associations 1,2,3', $logFile)->shouldBeCalled();

        $this->removeAssociatedProducts($event);
    }
}
