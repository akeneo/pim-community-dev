<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Service;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CreateUserInterface
{
    /**
     * @param string[] $groups
     * @param string[] $roles
     *
     * @return int user id
     */
    public function execute(string $username, string $name, array $groups, array $roles, string $appId): int;
}
