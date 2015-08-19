<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Repository;

use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

/**
 * Product cascade removal repository for MongoDBODM
 * Updates product document when an entity related to product is removed
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
interface ProductCascadeRemovalRepositoryInterface
{
    /**
     * Remove assets in product document
     *
     * @param AssetInterface $asset
     * @param array          $attributeCodes
     */
    public function cascadeAssetRemoval(AssetInterface $asset, array $attributeCodes);
}
