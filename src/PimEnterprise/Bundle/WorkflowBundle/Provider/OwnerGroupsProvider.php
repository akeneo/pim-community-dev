<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Provider;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;

/**
 * Class OwnerGroupsProvider
 *
 * Provides a set of user groups having owner permission of a product.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class OwnerGroupsProvider
{
    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /**
     * @param CategoryAccessRepository $categoryAccessRepo
     */
    public function __construct(CategoryAccessRepository $categoryAccessRepo)
    {
        $this->categoryAccessRepo = $categoryAccessRepo;
    }

    /**
     * Return the set of group ids owner of a product.
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    public function getOwnerGroupIds(ProductInterface $product)
    {
        $ownerGroupsId = [];
        $ownerGroups = $this->categoryAccessRepo->getGrantedUserGroupsForProduct($product, Attributes::OWN_PRODUCTS);
        foreach ($ownerGroups as $userGroup) {
            $ownerGroupsId[] = $userGroup['id'];
        }

        return $ownerGroupsId;
    }
}
