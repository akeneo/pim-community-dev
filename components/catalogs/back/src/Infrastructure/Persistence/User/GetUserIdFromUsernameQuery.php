<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\User;

use Akeneo\Catalogs\Application\Exception\UserNotFoundException;
use Akeneo\Catalogs\Application\Persistence\User\GetUserIdFromUsernameQueryInterface;
use Akeneo\UserManagement\ServiceApi\User\ListUsersHandlerInterface;
use Akeneo\UserManagement\ServiceApi\User\User;
use Akeneo\UserManagement\ServiceApi\User\UsersQuery;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUserIdFromUsernameQuery implements GetUserIdFromUsernameQueryInterface
{
    public function __construct(
        private ListUsersHandlerInterface $listUsersHandler,
    ) {
    }

    public function execute(string $username): int
    {
        /** @var array<User> $users */
        $users = $this->listUsersHandler->fromQuery(new UsersQuery(search: $username, limit: 1));
        if (\count($users) !== 1) {
            throw new UserNotFoundException();
        }

        return $users[0]->getId();
    }
}
