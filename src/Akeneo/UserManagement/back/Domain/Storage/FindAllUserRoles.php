<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Domain\Storage;

use Akeneo\UserManagement\Domain\Model\UserRole;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindAllUserRoles
{
    /**
     * @return UserRole[]
     */
    public function __invoke(): array;
}
