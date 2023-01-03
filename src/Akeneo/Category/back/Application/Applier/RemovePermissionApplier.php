<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use AkeneoEnterprise\Category\Api\Command\UserIntents\RemovePermission;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RemovePermissionApplier implements UserIntentApplier
{
    public function apply(UserIntent $userIntent, Category $category): void
    {
        if (!$userIntent instanceof RemovePermission) {
            throw new \InvalidArgumentException(sprintf('Unexpected class: %s', get_class($userIntent)));
        }

        $permissions = $category->getPermissions();

        if ($userIntent->userGroups()) {
            $permissions->removeUserGroupsFromPermission($userIntent->type(), $userIntent->userGroups());
        }
    }

    public function getSupportedUserIntents(): array
    {
        return [RemovePermission::class];
    }
}
