<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductValueInterface;
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
    /** @var ObjectManager */
    protected $objectManager;

    /** @var EntityManager */
    protected $entityManager;

    /**
     * @param ObjectManager $objectManager
     * @param EntityManager $entityManager
     */
    public function __construct(ObjectManager $objectManager, EntityManager $entityManager)
    {
        $this->objectManager = $objectManager;
        $this->entityManager = $entityManager;
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
            $this->objectManager->detach($publishedValue);
            $this->detachSpecificValues($publishedValue);
        }

        $this->objectManager->detach($published);
        foreach ($published->getCompletenesses() as $publishedComp) {
            $this->objectManager->detach($publishedComp);
        }
        foreach ($published->getAssociations() as $publishedAssoc) {
            $this->objectManager->detach($publishedAssoc);
        }

        $this->objectManager->detach($product);
        foreach ($product->getAssociations() as $assoc) {
            $this->objectManager->detach($assoc);
        }
        foreach ($product->getCompletenesses() as $comp) {
            $this->objectManager->detach($comp);
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
            case AttributeTypes::BACKEND_TYPE_MEDIA:
                if (null !== $publishedValue->getMedia()) {
                    $this->entityManager->detach($publishedValue->getMedia());
                }
                break;
            case AttributeTypes::BACKEND_TYPE_METRIC:
                if (null !== $publishedValue->getMetric()) {
                    $this->objectManager->detach($publishedValue->getMetric());
                }
                break;
            case AttributeTypes::BACKEND_TYPE_PRICE:
                if ($publishedValue->getPrices()->count() > 0) {
                    foreach ($publishedValue->getPrices() as $price) {
                        $this->objectManager->detach($price);
                    }
                }
                break;
        }
    }
}
