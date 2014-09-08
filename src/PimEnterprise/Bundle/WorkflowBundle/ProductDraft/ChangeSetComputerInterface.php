<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\ProductDraft;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Product change set computer during product draft workflow
 *
 * @author Gildas Quemener <gildas@akeneo.com>
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
