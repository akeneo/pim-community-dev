<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\User;

use Akeneo\Catalogs\Application\Exception\UserNotFoundException;
use Akeneo\Catalogs\Application\Persistence\User\GetUserIdFromUsernameQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUserIdFromUsernameQuery implements GetUserIdFromUsernameQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function execute(string $username): int
    {
        $sql = <<<SQL
        SELECT id
        FROM oro_user
        WHERE username = :username
SQL;

        $userId = $this->connection->fetchFirstColumn($sql, ['username' => $username]);
        if (\count($userId) !== 1) {
            throw new UserNotFoundException();
        }

        return (int)$userId[0];
    }
}
