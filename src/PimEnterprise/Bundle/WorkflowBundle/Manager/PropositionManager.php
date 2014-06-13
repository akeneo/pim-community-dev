<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use PimEnterprise\Bundle\WorkflowBundle\Persistence\ProductChangesApplier;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

/**
 * Manage product propositions
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionManager
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var ProductManager */
    protected $manager;

    /** @var ProductChangesApplier */
    protected $applier;

    /**
     * @param ManagerRegistry       $registry
     * @param ProductManager        $manager
     * @param ProductChangesApplier $applier
     */
    public function __construct(
        ManagerRegistry $registry,
        ProductManager $manager,
        ProductChangesApplier $applier
    ) {
        $this->registry = $registry;
        $this->manager = $manager;
        $this->applier = $applier;
    }

    /**
     * Approve a proposition
     *
     * @param Proposition $proposition
     */
    public function approve(Proposition $proposition)
    {
        $product = $proposition->getProduct();

        $this->applier->apply($product, $proposition->getChanges());

        $this->manager->handleMedia($product);
        $this->manager->saveProduct($product, ['bypass_proposition' => true]);
        $this->remove($proposition);
    }

    /**
     * Refuse a proposition
     *
     * @param Proposition $proposition
     */
    public function refuse(Proposition $proposition)
    {
        $this->remove($proposition);
    }

    /**
     * Remove a persisted proposition
     *
     * @param Proposition $proposition
     *
     */
    public function remove(Proposition $proposition)
    {
        $propositionManager = $this->registry->getManagerForClass(get_class($proposition));
        $propositionManager->remove($proposition);
        $propositionManager->flush();
    }
}
