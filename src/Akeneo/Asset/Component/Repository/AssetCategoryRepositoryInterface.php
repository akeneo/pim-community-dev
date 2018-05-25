<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Asset\Component\Repository;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\CategoryInterface;

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
