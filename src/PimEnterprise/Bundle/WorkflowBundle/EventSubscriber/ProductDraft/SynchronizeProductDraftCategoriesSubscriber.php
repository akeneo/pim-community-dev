<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

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
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * Keeps proposition categoryIds field synchronized with its related product's categories
 *
 * As events in mongodb-odm and orm are named identically, and as this subscriber is registered
 * inside mongodb-odm and orm event managers, then event received as parameter must be typehinted against
 * the common LifecycleEventArgs interface.
 *
 * This subscriber is only registered when the mongodb support is activated
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class SynchronizeProductDraftCategoriesSubscriber implements EventSubscriber
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var string */
    protected $propositionClassName;

    /**
     * @param ManagerRegistry $registry
     * @param string          $propositionClassName
     */
    public function __construct(ManagerRegistry $registry, $propositionClassName)
    {
        $this->registry = $registry;
        $this->propositionClassName = $propositionClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            // @codingStandardsIgnoreStart
            MongoDBODMEvents::prePersist,
            MongoDBODMEvents::preUpdate,
            ORMEvents::preRemove,
            // @codingStandardsIgnoreEnd
        ];
    }

    /**
     * Handle synchronization of propostion before proposition document insertion
     *
     * @param LifecycleEventArgs $event
     *
     * @return null
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        if (!$event instanceof MongoDBODMLifecycleEventArgs) {
            return;
        }
        $document = $event->getDocument();
        if ($document instanceof Proposition) {
            $this->syncProductProposition($document);
        }
    }

    /**
     * Handle synchronization of propostion(s) before proposition or product document update
     *
     * @param LifecycleEventArgs $event
     *
     * @return null
     */
    public function preUpdate(LifecycleEventArgs $event)
    {
        if (!$event instanceof MongoDBODMPreUpdateEventsArgs) {
            return;
        }
        $document = $event->getDocument();
        if ($document instanceof Proposition) {
            $this->syncProductProposition($document);
        } elseif ($document instanceof ProductInterface && $event->hasChangedField('categoryIds')) {
            $this->syncProductPropositions(
                $document,
                $event->getDocumentManager()->getUnitOfWork()
            );
        }
    }

    /**
     * Handle synchronization of propostion(s) before category entity removal
     *
     * @param LifecycleEventArgs $event
     *
     * @return null
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
     * @param Proposition $proposition
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
     * @param ProductInterface $product
     * @param UnitOfWork       $uow
     */
    protected function syncProductPropositions(ProductInterface $product, UnitOfWork $uow)
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
            $uow->scheduleExtraUpdate(
                $proposition,
                [
                    'categoryIds' => [
                        $proposition->getCategoryIds(),
                        $categoryIds
                    ]
                ]
            );
        }
    }

    /**
     * Get propositions related to a product
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getPropositions(ProductInterface $product)
    {
        return $this->registry->getRepository($this->propositionClassName)->findByProduct($product);
    }
}
