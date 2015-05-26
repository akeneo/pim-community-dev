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

use PimEnterprise\Component\ProductAsset\Model\ProductAssetReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;

/**
 * Builds references related to an asset
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface ProductAssetReferenceBuilderInterface
{
    /**
     * @param ProductAssetInterface $asset
     *
     * @return ProductAssetReferenceInterface[]
     */
    public function buildAllLocalized(ProductAssetInterface $asset);

    /**
     * @param ProductAssetInterface $asset
     *
     * @return ProductAssetReferenceInterface[]
     */
    public function buildMissingLocalized(ProductAssetInterface $asset);

    /**
     * @param ProductAssetInterface $asset
     * @param LocaleInterface|null  $locale
     *
     * @return ProductAssetReferenceInterface
     */
    public function buildOne(ProductAssetInterface $asset, LocaleInterface $locale = null);
}
