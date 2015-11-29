<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\VersioningBundle\Model\Version;
use Pim\Component\Catalog\Model\AbstractProduct;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ReferableInterface;

/**
 * Published product
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
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
