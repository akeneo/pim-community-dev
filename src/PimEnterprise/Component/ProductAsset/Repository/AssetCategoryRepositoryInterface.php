<?php

namespace PimEnterprise\Component\ProductAsset\Repository;

use Pim\Component\Classification\Repository\CategoryFilterableRepositoryInterface;
use Pim\Component\Classification\Repository\ItemCategoryRepositoryInterface;

/**
 * Asset category repository interface
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
interface AssetCategoryRepositoryInterface extends
    ItemCategoryRepositoryInterface,
    CategoryFilterableRepositoryInterface
{
}
