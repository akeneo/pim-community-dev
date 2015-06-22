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
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface as BaseProductValueInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

/**
 * Enterprise override of the Community product value interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductValueInterface extends BaseProductValueInterface
{
    /**
     * @return ArrayCollection
     */
    public function getAssets();

    /**
     * @param ArrayCollection $assets
     *
     * @return ProductValueInterface
     */
    public function setAssets(ArrayCollection $assets);

    /**
     * @param AssetInterface $asset
     *
     * @return ProductValueInterface
     */
    public function addAsset(AssetInterface $asset);

    /**
     * @param AssetInterface $asset
     *
     * @return ProductValueInterface
     */
    public function removeAsset(AssetInterface $asset);
}
