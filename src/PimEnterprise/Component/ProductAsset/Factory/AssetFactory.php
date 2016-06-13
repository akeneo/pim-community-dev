<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Factory;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;

/**
 * Asset factory
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AssetFactory implements SimpleFactoryInterface
{
    /** @var string */
    protected $assetClass;

    /**
     * @param string $assetClass
     */
    public function __construct($assetClass)
    {
        $this->assetClass       = $assetClass;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return new $this->assetClass();
    }
}
