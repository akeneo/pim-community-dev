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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Component\Classification\Repository\CategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;

/**
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 *
 * TODO: Remove this class for PIM-4292.
 *       We should update PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository to work
 *       with AssetCategories.
 */
class AssetCategoryAccessRepository extends CategoryAccessRepository
{
    /** @var CategoryRepositoryInterface */
    protected $assetCategoryRepo;

    /**
     * {@inherit}
     */
    public function isOwner(User $user)
    {
        return true;
    }

    /**
     * {@inherit}
     */
    public function isCategoriesGranted(User $user, $accessLevel, array $categoryIds)
    {
        return true;
    }

    /**
     * {@inherit}
     */
    public function getGrantedCategoryIds(User $user, $accessLevel)
    {
        $categories = $this->assetCategoryRepo->findAll();

        return array_map(function ($category) {
            return $category->getId();
        }, $categories);
    }

    /**
     * {@inherit}
     */
    public function getCategoryIdsWithExistingAccess($groups, $categoryIds)
    {
        $categories = $this->assetCategoryRepo->findAll();

        return array_map(function ($category) {
            return $category->getId();
        }, $categories);
    }

    /**
     * {@inherit}
     */
    public function getGrantedCategoryIdsFromQB(QueryBuilder $categoryQB, User $user, $accessLevel)
    {
        $categories = $this->assetCategoryRepo->findAll();

        return array_map(function ($category) {
            return $category->getId();
        }, $categories);
    }

    /**
     * {@inherit}
     */
    public function getRevokedCategoryIds(User $user, $accessLevel)
    {
        return [];
    }

    /**
     * @param CategoryRepositoryInterface $assetCategoryRepo
     */
    public function setAssetCategoryRepo($assetCategoryRepo)
    {
        $this->assetCategoryRepo = $assetCategoryRepo;
    }
}
