<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\ReferableInterface;
use Pim\Bundle\VersioningBundle\Entity\Version;

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

    /** @var  Version */
    protected $version;

    /**
     * {@inheritdoc}
     */
    public function getOriginalProductId()
    {
        return $this->originalProductId;
    }

    /**
     * {@inheritdoc}
     */
    public function setOriginalProductId($productId)
    {
        $this->originalProductId = $productId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function setVersion(Version $version)
    {
        $this->version = $version;

        return $this;
    }
}
