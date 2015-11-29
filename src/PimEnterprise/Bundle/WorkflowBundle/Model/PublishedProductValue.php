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

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Component\Catalog\Model\AbstractProductValue;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

/**
 * Published product value
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 * @ExclusionPolicy("all")
 */
class PublishedProductValue extends AbstractProductValue implements PublishedProductValueInterface
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
