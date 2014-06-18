<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\ReferableInterface;

/**
 * Published product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @ExclusionPolicy("all")
 */
class PublishedProduct extends AbstractProduct implements ReferableInterface, PublishedProductInterface
{
    /** @var mixed */
    protected $originalProductId;

    /**
     * @return mixed
     */
    public function getOriginalProductId()
    {
        return $this->originalProductId;
    }

    /**
     * @param mixed $productId
     *
     * @return PublishedProduct
     */
    public function setOriginalProductId($productId)
    {
        $this->originalProductId = $productId;

        return $this;
    }
}
