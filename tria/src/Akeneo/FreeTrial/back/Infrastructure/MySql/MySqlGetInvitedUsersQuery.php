<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\MySql;

use Akeneo\FreeTrial\Domain\Model\InvitedUser;
use Akeneo\FreeTrial\Domain\Query\GetInvitedUsersQuery;
use Akeneo\FreeTrial\Domain\ValueObject\InvitedUserStatus;
use Doctrine\DBAL\Connection;

final class MySqlGetInvitedUsersQuery implements GetInvitedUsersQuery
{
    private Connection $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(): array
    {
        $query = <<<SQL
SELECT email, status FROM akeneo_free_trial_invited_user ORDER BY created_at DESC;
SQL;
        $stmt = $this->dbalConnection->executeQuery($query);

        $invitedUsers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(function (array $invitedUser) {
            return new InvitedUser($invitedUser['email'], InvitedUserStatus::fromString($invitedUser['status']));
        }, $invitedUsers);
    }
}
