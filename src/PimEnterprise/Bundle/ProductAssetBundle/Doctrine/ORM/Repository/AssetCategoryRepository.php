<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\Repository;

use Pim\Bundle\ClassificationBundle\Doctrine\ORM\Repository\ItemCategoryRepository;
use PimEnterprise\Component\ProductAsset\Repository\AssetCategoryRepositoryInterface;

/**
 * Asset category repository
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class AssetCategoryRepository extends ItemCategoryRepository implements AssetCategoryRepositoryInterface
{
}
