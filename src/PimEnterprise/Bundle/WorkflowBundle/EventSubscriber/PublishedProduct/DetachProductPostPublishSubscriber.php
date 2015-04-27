<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This subscriber listen to post publish event in order to detach
 * the published product and the product itself to avoid
 * polluting the unit of work, thus avoiding very slow
 * mass publish process.
 *
 * @author Benoit Jacquemont <benoit@akeneo.com>
 */
class DetachProductPostPublishSubscriber implements EventSubscriberInterface
{
    /** @var ProductManager */
    protected $productManager;

    /**
     * @param ProductManager $productManager
     */
    public function __construct(ProductManager $productManager)
    {
        $this->productManager = $productManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PublishedProductEvents::POST_PUBLISH => 'detachProductPostPublish'
        ];
    }

    /**
     * Detach the product and the published product from the UoW
     *
     * @param PublishedProductEvent $event
     */
    public function detachProductPostPublish(PublishedProductEvent $event)
    {
        $product   = $event->getProduct();
        $published = $event->getPublishedProduct();

        foreach ($published->getValues() as $publishedValue) {
            $this->getObjectManager()->detach($publishedValue);
            $this->detachSpecificValues($publishedValue);
        }

        $this->getObjectManager()->detach($published);
        foreach ($published->getCompletenesses() as $publishedComp) {
            $this->getObjectManager()->detach($publishedComp);
        }
        foreach ($published->getAssociations() as $publishedAssoc) {
            $this->getObjectManager()->detach($publishedAssoc);
        }

        $this->getObjectManager()->detach($product);
        foreach ($product->getAssociations() as $assoc) {
            $this->getObjectManager()->detach($assoc);
        }
        foreach ($product->getCompletenesses() as $comp) {
            $this->getObjectManager()->detach($comp);
        }
    }

    /**
     * Detach specific values
     *
     * @param ProductValueInterface $publishedValue
     */
    protected function detachSpecificValues(ProductValueInterface $publishedValue)
    {
        switch ($publishedValue->getAttribute()->getBackendType()) {
            case AbstractAttributeType::BACKEND_TYPE_MEDIA:
                if (null !== $publishedValue->getMedia()) {
                    $this->getObjectManager()->detach($publishedValue->getMedia());
                }
                break;
            case AbstractAttributeType::BACKEND_TYPE_METRIC:
                if (null !== $publishedValue->getMetric()) {
                    $this->getObjectManager()->detach($publishedValue->getMetric());
                }
                break;
            case AbstractAttributeType::BACKEND_TYPE_PRICE:
                if ($publishedValue->getPrices()->count() > 0) {
                    foreach ($publishedValue->getPrices() as $price) {
                        $this->getObjectManager()->detach($price);
                    }
                }
                break;
        }
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->productManager->getObjectManager();
    }
}
