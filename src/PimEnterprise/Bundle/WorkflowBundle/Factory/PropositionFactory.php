<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Factory;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

/**
 * Product proposition factory
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionFactory
{
    /**
     * Create and configure a Proposition instance
     *
     * @param ProductInterface $product
     * @param string           $username
     * @param array            $changes
     *
     * @return Proposition
     */
    public function createProposition(ProductInterface $product, $username)
    {
        $proposition = new Proposition();
        $proposition
            ->setProduct($product)
            ->setAuthor($username)
            ->setCreatedAt(new \DateTime());

        return $proposition;
    }
}
