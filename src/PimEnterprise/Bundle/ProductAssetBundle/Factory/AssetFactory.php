<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Factory;

use PimEnterprise\Component\ProductAsset\Model\Asset;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

/**
 * Asset factory
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AssetFactory
{
    /** @var string */
    protected $assetClass;

    /**
     * @param string $assetClass
     */
    public function __construct($assetClass)
    {
        $this->assetClass = $assetClass;
    }

    /**
     * Create a new empty Asset
     *
     * @return AssetInterface
     */
    public function create()
    {
        return new $this->assetClass();
    }
}
