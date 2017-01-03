<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductTemplateBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Repository\ProductTemplateRepositoryInterface;
use Prophecy\Argument;

class ProductTemplateAttributeSubscriberSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        ProductTemplateBuilderInterface $productTplBuilder,
        ProductTemplateRepositoryInterface $productTplRepository
    ) {
        $this->beConstructedWith($objectManager, $productTplBuilder, $productTplRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\EventSubscriber\ProductTemplateAttributeSubscriber');
    }

    function it_subscribes_to_pre_remove_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_REMOVE => 'preRemove',
        ]);
    }

    function it_doesnt_apply_on_non_attribute_objects($objectManager, RemoveEvent $event, \stdClass $object)
    {
        $objectManager->persist(Argument::any())->shouldNotBeCalled();
        $objectManager->remove(Argument::any())->shouldNotBeCalled();
        $event->getSubject()->willReturn($object);

        $this->preRemove($event)->shouldReturn(null);
    }

    function it_applies_on_product_templates_containing_attribute_value(
        $objectManager,
        $productTplBuilder,
        $productTplRepository,
        RemoveEvent $event,
        AttributeInterface $object,
        ProductTemplateInterface $productTemplate1,
        ProductTemplateInterface $productTemplate2
    ) {
        // First will become empty so remove have to be called
        $objectManager->remove($productTemplate1)->shouldBeCalled();
        // Second will have to be persisted
        $objectManager->persist($productTemplate2)->shouldBeCalled();

        $productTemplate1->getAttributeCodes()->willReturn([]);
        $productTemplate2->getAttributeCodes()->willReturn(['code']);

        $productTplRepository->findByAttribute($object)->willReturn([$productTemplate1, $productTemplate2]);

        $productTplBuilder->removeAttribute($productTemplate1, $object)->shouldBeCalled();
        $productTplBuilder->removeAttribute($productTemplate2, $object)->shouldBeCalled();

        $event->getSubject()->willReturn($object);

        $this->preRemove($event)->shouldReturn(null);
    }
}
