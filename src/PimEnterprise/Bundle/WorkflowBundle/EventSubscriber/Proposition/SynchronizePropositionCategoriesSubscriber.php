<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs as MongoDBODMLifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs as MongoDBODMPreUpdateEventsArgs;
use Doctrine\ODM\MongoDB\Events as MongoDBODMEvents;
use Doctrine\ORM\Event\LifecycleEventArgs as ORMLifecycleEventsArgs;
use Doctrine\ORM\Events as ORMEvents;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

/**
 * Keeps proposition categoryIds field synchronized with its related product's categories
 *
 * As events in mongodb-odm and orm are named identically, and as this subscriber is registered
 * inside mongodb-odm and orm event managers, then event received as parameter must be typehinted against
 * the common LifecycleEventArgs interface.
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class SynchronizePropositionCategoriesSubscriber implements EventSubscriber
{
    protected $registry;
    protected $propositionClassName;

    public function __construct(ManagerRegistry $registry, $propositionClassName)
    {
        $this->registry = $registry;
        $this->propositionClassName = $propositionClassName;
    }

    public function getSubscribedEvents()
    {
        return [
            MongoDBODMEvents::prePersist,
            MongoDBODMEvents::preUpdate,
            ORMEvents::preRemove,
        ];
    }

    /**
     * Synchronize category ids of inserted propositions
     *
     * @param LifecycleEventArgs $event
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        if (!$event instanceof MongoDBODMLifecycleEventArgs) {
            return;
        }
        $document = $event->getDocument();
        if ($document instanceof Proposition) {
            return $this->syncProductProposition($document);
        }
    }

    /**
     *
     * @param LifecycleEventArgs $event
     */
    public function preUpdate(LifecycleEventArgs $event)
    {
        if (!$event instanceof MongoDBODMPreUpdateEventsArgs) {
            return;
        }
        $document = $event->getDocument();
        if ($document instanceof Proposition) {
            return $this->syncProductProposition($document);
        }
        if ($document instanceof ProductInterface && $event->hasChangedField('categoryIds')) {
            return $this->syncProductPropositions($document);
        }
    }

    /**
     * Synchronize category ids of propositions of products belonging to a removed category
     *
     * @param LifecycleEventArgs $event
     */
    public function preRemove(LifecycleEventArgs $event)
    {
        if (!$event instanceof ORMLifecycleEventsArgs) {
            return;
        }
        $category = $event->getEntity();
        if (!$category instanceof CategoryInterface) {
            return;
        }

        foreach ($category->getProducts() as $product) {
            foreach ($this->getPropositions($product) as $proposition) {
                $proposition->removeCategoryId($category->getId());
            }
        }
    }

    /**
     * Synchronize category ids of proposition
     *
     * @param MongoDBODMLifecycleEventArgs $event
     */
    protected function syncProductProposition(Proposition $proposition)
    {
        $categoryIds = $proposition
            ->getProduct()
            ->getCategories()
            ->map(
                function (CategoryInterface $category) {
                    return $category->getId();
                }
            )
            ->toArray();
        $proposition->setCategoryIds($categoryIds);
    }

    /**
     * Synchronize category ids of propositions of product of which categories has been changed
     *
     * @param MongoDBODMPreUpdateEventsArgs $event
     */
    protected function syncProductPropositions(ProductInterface $product)
    {
        $categoryIds = $product
            ->getCategories()
            ->map(
                function (CategoryInterface $category) {
                    return $category->getId();
                }
            )
            ->toArray();

        $propositions = $this->getPropositions($product);
        foreach ($propositions as $proposition) {
            $proposition->setCategoryIds($categoryIds);
        }
    }

    public function getPropositions(ProductInterface $product)
    {
        return $this->registry->getRepository($this->propositionClassName)->findByProduct($product);
    }
}
