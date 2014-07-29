<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ReferableInterface;
use Pim\Bundle\VersioningBundle\Model\Version;

/**
 * Published product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @ExclusionPolicy("all")
 */
class PublishedProduct extends AbstractProduct implements ReferableInterface, PublishedProductInterface
{
    /** @var ProductInterface */
    protected $originalProduct;

    /** @var  Version */
    protected $version;

    /**
     * {@inheritdoc}
     */
    public function getOriginalProduct()
    {
        return $this->originalProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function setOriginalProduct(ProductInterface $productId)
    {
        $this->originalProduct = $productId;

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
