<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Model;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Published product interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface PublishedProductInterface extends ProductInterface
{
    /**
     * @return mixed
     */
    public function getOriginalProductId();

    /**
     * @param mixed $productId
     *
     * @return PublishedProduct
     */
    public function setOriginalProductId($productId);
}
