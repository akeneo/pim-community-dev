<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\ProductValue as BaseProductValue;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

/**
 * Enterprise override of the Community product value
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValue extends BaseProductValue implements ProductValueInterface
{
    /** @var ArrayCollection */
    protected $assets;

    /** @var array (used only in MongoDB implementation) */
    protected $assetIds;

    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->assets = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * {@inheritdoc}
     */
    public function setAssets(ArrayCollection $assets)
    {
        $this->assets = $assets;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAsset(AssetInterface $asset)
    {
        $this->assets->add($asset);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAsset(AssetInterface $asset)
    {
        $this->assets->removeElement($asset);

        return $this;
    }
}
