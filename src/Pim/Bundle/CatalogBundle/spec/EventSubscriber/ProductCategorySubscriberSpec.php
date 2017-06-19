<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;

class ProductCategorySubscriberSpec extends ObjectBehavior
{
    function let(BulkSaverInterface $saver)
    {
        $this->beConstructedWith($saver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\EventSubscriber\ProductCategorySubscriber');
    }

    function it_subscribes_to_pre_remove_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_REMOVE => 'postRemove',
        ]);
    }

    function it_doesnt_apply_on_non_category_objects($saver, RemoveEvent $event, \stdClass $object)
    {
        $saver->saveAll()->shouldNotBeCalled();
        $event->getSubject()->willReturn($object);

        $this->postRemove($event)->shouldReturn(null);
    }

    function it_doesnt_apply_without_related_products($saver, RemoveEvent $event, CategoryInterface $object)
    {
        $saver->saveAll()->shouldNotBeCalled();
        $event->getSubject()->willReturn($object);
        $object->getProducts()->willReturn([]);

        $this->postRemove($event)->shouldReturn(null);
    }

    function it_applies_on_related_products($saver, RemoveEvent $event, CategoryInterface $object, ProductInterface $product)
    {
        $saver->saveAll([$product])->shouldBeCalled();

        $event->getSubject()->willReturn($object);
        $object->getProducts()->willReturn([$product]);

        $product->removeCategory($object)->shouldBeCalled();

        $this->postRemove($event)->shouldReturn(null);
    }
}
