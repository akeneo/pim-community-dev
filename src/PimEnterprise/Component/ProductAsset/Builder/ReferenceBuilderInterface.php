<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Builder;

use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;

/**
 * Builds references related to an asset
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface ReferenceBuilderInterface
{
    /**
     * @param AssetInterface $asset
     *
     * @return ReferenceInterface[]
     */
    public function buildAllLocalized(AssetInterface $asset);

    /**
     * @param AssetInterface $asset
     *
     * @return ReferenceInterface[]
     */
    public function buildMissingLocalized(AssetInterface $asset);

    /**
     * @param AssetInterface       $asset
     * @param LocaleInterface|null $locale
     *
     * @return ReferenceInterface
     */
    public function buildOne(AssetInterface $asset, LocaleInterface $locale = null);
}
