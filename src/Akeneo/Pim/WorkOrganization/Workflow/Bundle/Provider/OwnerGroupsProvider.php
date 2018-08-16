<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Provider;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;

/**
 * Provides a set of user groups having owner permission of a product.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class OwnerGroupsProvider
{
    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    public function __construct(CategoryAccessRepository $categoryAccessRepo)
    {
        $this->categoryAccessRepo = $categoryAccessRepo;
    }

    public function getOwnerGroupIds(EntityWithValuesInterface $entityWithValues): array
    {
        $ownerGroupsId = [];
        $ownerGroups = $this->categoryAccessRepo->getGrantedUserGroupsForEntityWithValues($entityWithValues, Attributes::OWN_PRODUCTS);
        foreach ($ownerGroups as $userGroup) {
            $ownerGroupsId[] = $userGroup['id'];
        }

        return $ownerGroupsId;
    }
}
