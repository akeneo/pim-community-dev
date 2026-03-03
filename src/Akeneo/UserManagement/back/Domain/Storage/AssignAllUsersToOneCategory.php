<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Domain\Storage;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AssignAllUsersToOneCategory
{
    public function execute(int $categoryId): int;
}
