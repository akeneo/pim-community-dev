<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use PimEnterprise\Bundle\WorkflowBundle\Persistence\ProductChangesApplier;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposal;

/**
 * Manage product proposals
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalManager
{
    protected $registry;

    protected $applier;

    public function __construct(ManagerRegistry $registry, ProductChangesApplier $applier)
    {
        $this->registry = $registry;
        $this->applier = $applier;
    }

    public function approve(Proposal $proposal)
    {
        $product = $proposal->getProduct();
        foreach ($proposal->getChanges() as $key => $data) {
            $this->applier->apply($product, $key, $data);
        }

        $proposal->setStatus(Proposal::APPROVED);

        $this->registry->getManagerForClass(get_class($product))->flush();
        $this->registry->getManagerForClass(get_class($proposal))->flush();
    }
}
