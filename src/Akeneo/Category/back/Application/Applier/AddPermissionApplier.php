<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\AddPermission;
use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Domain\Model\Enrichment\Category;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddPermissionApplier implements UserIntentApplier
{
    public function apply(UserIntent $userIntent, Category $category): void
    {
        if (!$userIntent instanceof AddPermission) {
            throw new \InvalidArgumentException(sprintf('Unexpected class: %s', get_class($userIntent)));
        }

        $permissions = $category->getPermissions();

        $permissions->addPermission($userIntent->type(), $userIntent->userGroupIds());
    }

    public function getSupportedUserIntents(): array
    {
        return [AddPermission::class];
    }
}
