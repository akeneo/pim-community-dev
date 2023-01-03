<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Category\Application\Storage\Save\Saver;

use Akeneo\Category\Application\Storage\Save\Saver\CategorySaver;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Pim\Permission\Bundle\ServiceApi\Category\SaveCategoryProductPermissionsInterface;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class CategoryPermissionSaver implements CategorySaver
{
    /**
     * @param string[] $supportedUserIntents
     */
    public function __construct(
        private readonly SaveCategoryProductPermissionsInterface $saveCategoryProductPermissions,
        private readonly array $supportedUserIntents,
    ) {
    }

    public function save(Category $category): void
    {
        $categoryId = $category->getId()->getValue();
        $userGroupsIds = $category->getPermissions()->getUserGroupIdsPerPermission();

        ($this->saveCategoryProductPermissions)($categoryId, $userGroupsIds);
    }

    public function getSupportedUserIntents(): array
    {
        return $this->supportedUserIntents;
    }
}
