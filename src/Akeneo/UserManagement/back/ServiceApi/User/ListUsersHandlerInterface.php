<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Community Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\UserManagement\ServiceApi\User;

interface ListUsersHandlerInterface
{
    /**
     * @return User[]
     */
    public function fromQuery(UsersQuery $query): array;
}
