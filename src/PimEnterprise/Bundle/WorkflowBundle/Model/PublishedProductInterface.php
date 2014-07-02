<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Model;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Entity\Version;

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

    /**
     * @return Version
     */
    public function getVersion();

    /**
     * @param Version $version
     *
     * @return PublishedProductInterface
     */
    public function setVersion(Version $version);
}
