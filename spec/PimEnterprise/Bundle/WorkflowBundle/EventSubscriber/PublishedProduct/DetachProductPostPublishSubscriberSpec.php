<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvents;

class DetachProductPostPublishSubscriberSpec extends ObjectBehavior
{
    function let(ProductManager $productManager)
    {
        $this->beConstructedWith($productManager);
    }

    function it_subscribes_to_post_publish_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            PublishedProductEvents::POST_PUBLISH => 'detachProductPostPublish',
        ]);
    }

    function it_detachs_products_with_metric_value(
        $productManager,
        PublishedProductEvent $event,
        ObjectManager $objectManager,
        ProductInterface $product,
        ProductInterface $publishedProduct
    ) {
        $productManager->getObjectManager()->willReturn($objectManager);

        $metric = new ProductValue();
        $attribute = new Attribute();
        $attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_METRIC);
        $metric->setAttribute($attribute);

        $product->getValues()->willReturn([$metric]);
        $product->getCompletenesses()->willReturn(new ArrayCollection());
        $product->getAssociations()->willReturn(new ArrayCollection());
        $event->getProduct()->willReturn($product);

        $publishedMetric = new ProductValue();
        $attribute = new Attribute();
        $attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_METRIC);
        $publishedMetric->setAttribute($attribute);
        $publishedProduct->getValues()->willReturn([$publishedMetric]);
        $publishedProduct->getCompletenesses()->willReturn(new ArrayCollection());
        $publishedProduct->getAssociations()->willReturn(new ArrayCollection());
        $event->getPublishedProduct()->willReturn($publishedProduct);

        $objectManager->detach($publishedMetric)->shouldBeCalled();
        $objectManager->detach($publishedProduct)->shouldBeCalled();
        $objectManager->detach($metric)->shouldBeCalled();
        $objectManager->detach($product)->shouldBeCalled();

        $this->detachProductPostPublish($event);
    }

    function it_detachs_products_with_media_value(
        $productManager,
        PublishedProductEvent $event,
        ObjectManager $objectManager,
        ProductInterface $product,
        ProductInterface $publishedProduct
    ) {
        $productManager->getObjectManager()->willReturn($objectManager);

        $media = new ProductValue();
        $attribute = new Attribute();
        $attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_MEDIA);
        $media->setAttribute($attribute);

        $product->getValues()->willReturn([$media]);
        $product->getCompletenesses()->willReturn(new ArrayCollection());
        $product->getAssociations()->willReturn(new ArrayCollection());
        $event->getProduct()->willReturn($product);

        $publishedMedia = new ProductValue();
        $attribute = new Attribute();
        $attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_MEDIA);
        $publishedMedia->setAttribute($attribute);
        $publishedProduct->getValues()->willReturn([$publishedMedia]);
        $publishedProduct->getCompletenesses()->willReturn(new ArrayCollection());
        $publishedProduct->getAssociations()->willReturn(new ArrayCollection());
        $event->getPublishedProduct()->willReturn($publishedProduct);

        $objectManager->detach($publishedMedia)->shouldBeCalled();
        $objectManager->detach($publishedProduct)->shouldBeCalled();
        $objectManager->detach($media)->shouldBeCalled();
        $objectManager->detach($product)->shouldBeCalled();

        $this->detachProductPostPublish($event);
    }

    function it_detachs_products_with_prices_values(
        $productManager,
        PublishedProductEvent $event,
        ObjectManager $objectManager,
        ProductInterface $product,
        ProductInterface $publishedProduct
    ) {
        $productManager->getObjectManager()->willReturn($objectManager);

        $price = new ProductValue();
        $attribute = new Attribute();
        $attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_METRIC);
        $price->setAttribute($attribute);

        $product->getValues()->willReturn([$price]);
        $product->getCompletenesses()->willReturn(new ArrayCollection());
        $product->getAssociations()->willReturn(new ArrayCollection());
        $event->getProduct()->willReturn($product);

        $publishedPrice = new ProductValue();
        $attribute = new Attribute();
        $attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_METRIC);
        $publishedPrice->setAttribute($attribute);
        $publishedProduct->getValues()->willReturn([$publishedPrice]);
        $publishedProduct->getCompletenesses()->willReturn(new ArrayCollection());
        $publishedProduct->getAssociations()->willReturn(new ArrayCollection());
        $event->getPublishedProduct()->willReturn($publishedProduct);

        $objectManager->detach($publishedPrice)->shouldBeCalled();
        $objectManager->detach($publishedProduct)->shouldBeCalled();
        $objectManager->detach($price)->shouldBeCalled();
        $objectManager->detach($product)->shouldBeCalled();

        $this->detachProductPostPublish($event);
    }
}
