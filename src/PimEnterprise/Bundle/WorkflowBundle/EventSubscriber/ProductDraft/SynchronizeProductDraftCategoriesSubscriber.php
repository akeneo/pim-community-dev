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
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * Keeps product draft categoryIds field synchronized with its related product's categories
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
    protected $productDraftClassName;

    /**
     * @param ManagerRegistry $registry
     * @param string          $productDraftClassName
     */
    public function __construct(ManagerRegistry $registry, $productDraftClassName)
    {
        $this->registry = $registry;
        $this->productDraftClassName = $productDraftClassName;
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
     * Handle synchronization of propostion before product draft document insertion
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
        if ($document instanceof ProductDraft) {
            $this->syncProductProposition($document);
        }
    }

    /**
     * Handle synchronization of propostion(s) before product draft or product document update
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
        if ($document instanceof ProductDraft) {
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
            foreach ($this->getPropositions($product) as $productDraft) {
                $productDraft->removeCategoryId($category->getId());
            }
        }
    }

    /**
     * Synchronize category ids of product draft
     *
     * @param ProductDraft $productDraft
     */
    protected function syncProductProposition(ProductDraft $productDraft)
    {
        $categoryIds = $productDraft
            ->getProduct()
            ->getCategories()
            ->map(
                function (CategoryInterface $category) {
                    return $category->getId();
                }
            )
            ->toArray();
        $productDraft->setCategoryIds($categoryIds);
    }

    /**
     * Synchronize category ids of product drafts of product of which categories has been changed
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

        $productDrafts = $this->getPropositions($product);
        foreach ($productDrafts as $productDraft) {
            $uow->scheduleExtraUpdate(
                $productDraft,
                [
                    'categoryIds' => [
                        $productDraft->getCategoryIds(),
                        $categoryIds
                    ]
                ]
            );
        }
    }

    /**
     * Get product drafts related to a product
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getPropositions(ProductInterface $product)
    {
        return $this->registry->getRepository($this->productDraftClassName)->findByProduct($product);
    }
}
