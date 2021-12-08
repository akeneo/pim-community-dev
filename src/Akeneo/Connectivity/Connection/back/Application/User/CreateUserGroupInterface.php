<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\User;

use Akeneo\UserManagement\Component\Model\GroupInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CreateUserGroupInterface
{
    public function execute(string $groupName): GroupInterface;
}
