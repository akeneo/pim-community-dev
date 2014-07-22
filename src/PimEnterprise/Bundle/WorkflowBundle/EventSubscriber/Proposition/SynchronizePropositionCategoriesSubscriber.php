<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition;

use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionRepositoryInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\ManagerRegistry;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class SynchronizePropositionCategoriesSubscriber implements EventSubscriber
{

    public function __construct(
        ManagerRegistry $registry,
        $categoryAccessClassName,
        $propositionClassName
    ) {
        // TODO (2014-07-21 19:13 by Gildas): retrieve repositories from document manager
        $this->registry = $registry;
        $this->categoryAccessClassName = $categoryAccessClassName;
        $this->propositionClassName = $propositionClassName;
    }

    public function getSubscribedEvents()
    {
        return [
            #Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $product = $event->getDocument();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $this->synchronize($product);
    }

    public function preUpdate(LifecycleEventArgs $event)
    {
        $product = $event->getDocument();
        if (!$product instanceof ProductInterface) {
            return;
        }

        // TODO (2014-07-22 10:04 by Gildas): Do it only if product categories have changed
        $this->synchronize($product);
    }

    protected function synchronize(ProductInterface $product)
    {
        $roles = [];
        foreach ($product->getCategories() as $category) {
            foreach ($this->getAccessRepository()->getGrantedRoles($category, Attributes::OWN_PRODUCTS) as $role) {
                $roles[$role->getRole()] = null;
            }
        }

        $propositions = $this->getPropositionRepository()->findBy(['product.id' => $product->getId()]);
        foreach ($propositions as $proposition) {
            $proposition->setReviewers(array_keys($roles));
        }
    }

    protected function getAccessRepository()
    {
        return $this->registry->getRepository($this->categoryAccessClassName);
    }

    protected function getPropositionRepository()
    {
        return $this->registry->getRepository($this->propositionClassName);
    }
}
