<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Proposition;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Product change set computer during proposition workflow
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface ChangeSetComputerInterface
{
    /**
     * Compute the changes brought by submitted data against a product
     *
     * @param ProductInterface $product
     * @param array            $submittedData
     *
     * @return array
     */
    public function compute(ProductInterface $product, array $submittedData);
}
