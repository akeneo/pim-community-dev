<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValue;
use PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvents;

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

    function it_detachs_products_with_metric_value(
        $productManager,
        PublishedProductEvent $event,
        ProductInterface $product,
        ProductInterface $publishedProduct
    ) {
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

        $productManager->detach($publishedMetric)->shouldBeCalled();
        $productManager->detach($publishedProduct)->shouldBeCalled();
        $productManager->detach($metric)->shouldBeCalled();
        $productManager->detach($product)->shouldBeCalled();

        $this->detachProductPostPublish($event);
    }

    function it_detachs_products_with_media_value(
        $productManager,
        PublishedProductEvent $event,
        ProductInterface $product,
        ProductInterface $publishedProduct
    ) {
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

        $productManager->detach($publishedMedia)->shouldBeCalled();
        $productManager->detach($publishedProduct)->shouldBeCalled();
        $productManager->detach($media)->shouldBeCalled();
        $productManager->detach($product)->shouldBeCalled();

        $this->detachProductPostPublish($event);
    }

    function it_detachs_products_with_prices_values(
        $productManager,
        PublishedProductEvent $event,
        ProductInterface $product,
        ProductInterface $publishedProduct
    ) {
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

        $productManager->detach($publishedPrice)->shouldBeCalled();
        $productManager->detach($publishedProduct)->shouldBeCalled();
        $productManager->detach($price)->shouldBeCalled();
        $productManager->detach($product)->shouldBeCalled();

        $this->detachProductPostPublish($event);
    }
}
