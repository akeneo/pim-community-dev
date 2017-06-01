<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Akeneo\Component\StorageUtils\StorageEvents;
use Akeneo\Component\Versioning\Model\VersionableInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class TimestampableSubscriberSpec extends ObjectBehavior
{
    function it_is_a_syrmfony_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_pre_save_storage_event()
    {
        $this->getSubscribedEvents()->shouldReturn([StorageEvents::PRE_SAVE => 'updateProductTimestamp']);
    }

    function it_does_not_apply_pre_save_to_non_product_object(GenericEvent $event, NonTimestampableInterface $object)
    {
        $event->getSubject()->willReturn($object);
        $object->setCreated()->shouldNotBeCalled();
        $object->setUpdated()->shouldNotBeCalled();

        $this->updateProductTimestamp($event);
    }

    function it_applies_during_pre_save_on_new_product_object(GenericEvent $event, ProductInterface $product)
    {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(null);

        $product->setCreated(Argument::type('\DateTime'))->shouldBeCalled();
        $product->setUpdated(Argument::type('\DateTime'))->shouldBeCalled();

        $this->updateProductTimestamp($event);
    }

    function it_applies_during_pre_save_on_updated_product_object(GenericEvent $event, ProductInterface $product)
    {
        $product->getId()->willReturn('sku-1');
        $event->getSubject()->willReturn($product);

        $product->setCreated()->shouldNotBeCalled();
        $product->setUpdated(Argument::type('\DateTime'))->shouldBeCalled();

        $this->updateProductTimestamp($event);
    }
}

interface NonTimestampableInterface
{
    /**
     * @param \DateTime $updated
     */
    public function setUpdated(\DateTime $updated);

    /**
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created);
}

interface NonTimestampableVersionableInterface extends VersionableInterface
{
    /**
     * @param \DateTime $updated
     */
    public function setUpdated(\DateTime $updated);

    /**
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created);
}

