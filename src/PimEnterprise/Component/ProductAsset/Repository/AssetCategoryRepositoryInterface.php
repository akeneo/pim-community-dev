<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PimEnterprise\Component\ProductAsset\Repository;

use Pim\Component\User\Model\UserInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;

/**
 * Asset category repository interface
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
interface AssetCategoryRepositoryInterface
{
    /**
     * Get item count by granted tree
     *
     * @param AssetInterface $asset
     * @param UserInterface  $user
     *
     * @return array
     */
    public function getItemCountByGrantedTree(AssetInterface $asset, UserInterface $user);

    /**
     * Get the root elements of the tree
     *
     * @return CategoryInterface[]
     */
    public function findRoot();
}
