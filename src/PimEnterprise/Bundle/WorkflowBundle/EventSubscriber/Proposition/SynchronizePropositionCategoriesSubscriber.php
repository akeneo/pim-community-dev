<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionRepositoryInterface;

/**
 * Keeps proposition categoryIds field synchronized with its related product's categories
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class SynchronizePropositionCategoriesSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
            Events::preRemove,
        ];
    }

    /**
     * Synchronize category ids of propositions of product of which categories has been changed
     *
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(PreUpdateEventArgs $event)
    {
        $product = $event->getDocument();
        if (!$product instanceof ProductInterface) {
            return;
        }
        if (!$event->hasChangedField('categoryIds')) {
            return;
        }

        $this->synchronize($product);
    }

    public function preRemove(LifecycleEventArgs $event)
    {
        $category = $event->getDocument();
        if (!$category instanceof CategoryInterface) {
            return;
        }

        # Synchronize all category products
    }

    protected function synchronize(ProductInterface $product)
    {
        $categoryIds = $product
            ->getCategories()
            ->map(
                function (CategoryInterface $category) {
                    return $category->getId();
                }
            )
            ->toArray();

        foreach ($product->getPropositions() as $proposition) {
            $proposition->setCategoryIds($categoryIds);
        }
    }
}
