<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\Metric;
use Pim\Component\Catalog\Model\PriceCollection;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValue;
use PimEnterprise\Component\Workflow\Event\PublishedProductEvent;
use PimEnterprise\Component\Workflow\Event\PublishedProductEvents;

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
        $attribute->setBackendType(AttributeTypes::BACKEND_TYPE_TEXT);

        $value = new ProductValue($attribute, null, null, null);

        $product->getValues()->willReturn([$value]);
        $product->getCompletenesses()->willReturn(new ArrayCollection());
        $product->getAssociations()->willReturn(new ArrayCollection());
        $event->getProduct()->willReturn($product);

        $publishedValue = new ProductValue($attribute, null, null, null);

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
