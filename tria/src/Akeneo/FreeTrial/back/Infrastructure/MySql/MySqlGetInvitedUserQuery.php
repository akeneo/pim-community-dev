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
use Akeneo\FreeTrial\Domain\Query\GetInvitedUserQuery;
use Akeneo\FreeTrial\Domain\ValueObject\InvitedUserStatus;
use Doctrine\DBAL\Connection;

final class MySqlGetInvitedUserQuery implements GetInvitedUserQuery
{
    private Connection $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute($email): ?InvitedUser
    {
        $query = <<<SQL
SELECT email, status FROM akeneo_free_trial_invited_user WHERE email = :email AND status = :status;
SQL;
        $stmt = $this->dbalConnection->executeQuery($query, [
            'email' => $email,
            'status' => InvitedUserStatus::INVITED,
        ]);
        $invitedUser = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$invitedUser) {
            return null;
        }

        return new InvitedUser($invitedUser['email'], InvitedUserStatus::fromString($invitedUser['status']));
    }
}
