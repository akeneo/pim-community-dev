<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Pim\Component\Catalog\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\PublishedProductEvent;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\PublishedProductEvents;

class DetachProductPostPublishSubscriberSpec extends ObjectBehavior
{
    function let(ObjectManager $productManager, EntityManager $entityManager)
    {
        $this->beConstructedWith($productManager, $entityManager);
    }

    function it_subscribes_to_post_publish_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            PublishedProductEvents::POST_PUBLISH => 'detachProductPostPublish',
        ]);
    }

    function it_detachs_products_with_value(
        $productManager,
        PublishedProductEvent $event,
        ProductInterface $product,
        ProductInterface $publishedProduct
    ) {
        $attribute = new Attribute();
        $attribute->setBackendType(AttributeTypes::BACKEND_TYPE_TEXTAREA);

        $value = new ScalarValue($attribute, null, null, null);

        $product->getValues()->willReturn([$value]);
        $product->getCompletenesses()->willReturn(new ArrayCollection());
        $product->getAssociations()->willReturn(new ArrayCollection());
        $event->getProduct()->willReturn($product);

        $publishedValue = new ScalarValue($attribute, null, null, null);

        $publishedProduct->getValues()->willReturn([$publishedValue]);
        $publishedProduct->getCompletenesses()->willReturn(new ArrayCollection());
        $publishedProduct->getAssociations()->willReturn(new ArrayCollection());
        $event->getPublishedProduct()->willReturn($publishedProduct);

        $productManager->detach($publishedValue)->shouldBeCalled();
        $productManager->detach($publishedProduct)->shouldBeCalled();
        $productManager->detach($value)->shouldBeCalled();
        $productManager->detach($product)->shouldBeCalled();

        $this->detachProductPostPublish($event);
    }
}
