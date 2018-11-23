<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\PublishedProductEvent;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\PublishedProductEvents;
use Doctrine\Common\Persistence\ObjectManager;
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

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
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
        $product = $event->getProduct();
        $published = $event->getPublishedProduct();

        foreach ($published->getValues() as $publishedValue) {
            $this->objectManager->detach($publishedValue);
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
}
