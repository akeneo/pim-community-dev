<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use PimEnterprise\Bundle\WorkflowBundle\Persistence\ProductChangesApplier;
use PimEnterprise\Bundle\WorkflowBundle\Persistence\ProposalPersister;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposal;

/**
 * Manage product proposals
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalManager
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var ProposalPersister */
    protected $persister;

    /** @var ProductChangesApplier */
    protected $applier;

    /**
     * @param ManagerRegistry       $registry
     * @param ProposalPersister     $persister
     * @param ProductChangesApplier $applier
     */
    public function __construct(
        ManagerRegistry $registry,
        ProposalPersister $persister,
        ProductChangesApplier $applier
    ) {
        $this->registry = $registry;
        $this->persister = $persister;
        $this->applier = $applier;
    }

    /**
     * Approve a proposal
     *
     * @param Proposal $proposal
     */
    public function approve(Proposal $proposal)
    {
        $product = $proposal->getProduct();

        $this->applier->apply($product, $proposal->getChanges());

        $proposal->setStatus(Proposal::APPROVED);

        // Proposal and product doctrine persister is the same
        $this->persister->persist($product, ['bypass_proposal' => true]);
        $this->registry->getManagerForClass(get_class($proposal))->flush();
    }

    /**
     * Refuse a proposal
     *
     * @param Proposal $proposal
     */
    public function refuse(Proposal $proposal)
    {
        $proposal->setStatus(Proposal::REFUSED);

        $this->registry->getManagerForClass(get_class($proposal))->flush();
    }
}
