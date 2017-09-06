<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\Console\CommandLauncher;
use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\EventSubscriber\DeleteProductModelSubscriber;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class DeleteProductModelSubscriberSpec extends ObjectBehavior
{
    function let(RemoverInterface $remover) {
        $this->beConstructedWith($remover);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DeleteProductModelSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_REMOVE   => 'deleteProductModel',
        ]);
    }

    function it_deletes_product_model_descendants_from_elasticsearch_index(
        $remover,
        RemoveEvent $event,
        ProductModelInterface $productModel
    ) {
        $event->getSubject()->willReturn($productModel);

        $remover->remove($productModel)->shouldBeCalled();

        $this->deleteProductModel($event)->shouldReturn(null);
    }

    function it_does_not_delete_non_product_model_entity_from_elasticsearch($remover, RemoveEvent $event, \stdClass $subject)
    {
        $event->getSubject()->willReturn($subject);

        $remover->remove(40)->shouldNotBeCalled();

        $this->deleteProductModel($event)->shouldReturn(null);
    }
}
