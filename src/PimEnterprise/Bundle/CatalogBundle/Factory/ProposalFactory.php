<?php

namespace PimEnterprise\Bundle\CatalogBundle\Factory;

use Symfony\Component\Security\Core\User\UserInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\CatalogBundle\Model\Proposal;

/**
 * PimEnterprise\Bundle\CatalogBundle\Factory
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
     * @param UserInterface    $user
     * @param array            $changes
     *
     * @return Proposal
     */
    public function createProposal(ProductInterface $product, UserInterface $user, array $changes)
    {
        $proposal = new Proposal();
        $proposal->setProduct($product);
        $proposal->setCreatedBy($user);
        $proposal->setCreatedAt(new \DateTime());
        $proposal->setChanges($changes);

        return $proposal;
    }
}
