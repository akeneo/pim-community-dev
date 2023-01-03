<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Category\Application\Storage\Save\Remover;

use Akeneo\Category\Application\Storage\Save\Saver\CategorySaver;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Pim\Permission\Bundle\ServiceApi\Category\RemoveCategoryProductPermissionsInterface;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class CategoryPermissionRemover implements CategorySaver
{
    /**
     * @param string[] $supportedUserIntents
     */
    public function __construct(
        private readonly RemoveCategoryProductPermissionsInterface $removeCategoryProductPermissions,
        private readonly array $supportedUserIntents,
    ) {
    }

    public function save(Category $category): void
    {
        $categoryId = $category->getId()->getValue();
        $userGroupsIdsToRemove = $category->getPermissions()->getRemovedUserGroupIdsFromPermissions();

        ($this->removeCategoryProductPermissions)($categoryId, $userGroupsIdsToRemove);
    }

    public function getSupportedUserIntents(): array
    {
        return $this->supportedUserIntents;
    }
}
