<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Akeneo\Component\StorageUtils\StorageEvents;
use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Event\ProductEvents;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class RemoveOutdatedProductsFromAssociationsSubscriberSpec extends ObjectBehavior
{
    function let(ProductRepositoryInterface $productRepository, AssociationTypeRepositoryInterface $assocTypeRepository)
    {
        $this->beConstructedWith($productRepository, $assocTypeRepository);
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
                ProductEvents::POST_MASS_REMOVE => 'removeAssociatedProducts'
            ]
        );
    }

    function it_removed_associated_product(
        $productRepository,
        $assocTypeRepository,
        RemoveEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);
        $event->getSubjectId()->willReturn(2);

        $assocTypeRepository->countAll()->willReturn(5);
        $productRepository->removeAssociatedProduct(2, 5)->shouldBeCalled();

        $this->removeAssociatedProduct($event);
    }

    function it_removed_associated_products_on_many_products(
        $productRepository,
        $assocTypeRepository,
        RemoveEvent $event
    ) {
        $event->getSubject()->willReturn([1, 2, 3]);
        $assocTypeRepository->countAll()->willReturn(5);

        $productRepository->removeAssociatedProduct(1, 5)->shouldBeCalled();
        $productRepository->removeAssociatedProduct(2, 5)->shouldBeCalled();
        $productRepository->removeAssociatedProduct(3, 5)->shouldBeCalled();

        $this->removeAssociatedProducts($event);
    }
}
