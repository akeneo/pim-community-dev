<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Factory;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposal;

/**
 * Product proposal factory
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalFactory
{
    /**
     * Create and configure a Proposal instance
     *
     * @param ProductInterface $product
     * @param string           $username
     * @param array            $changes
     *
     * @return Proposal
     */
    public function createProposal(ProductInterface $product, $username, array $changes)
    {
        $proposal = new Proposal();
        $proposal
            ->setProduct($product)
            ->setAuthor($username)
            ->setCreatedAt(new \DateTime())
            ->setChanges($changes);

        return $proposal;
    }
}
