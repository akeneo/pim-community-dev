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
use Akeneo\FreeTrial\Domain\Repository\InvitedUserRepository;
use Doctrine\DBAL\Connection;

final class MySqlInvitedUserRepository implements InvitedUserRepository
{
    private Connection $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function save(InvitedUser $invitedUser): void
    {
        $query = <<<SQL
INSERT INTO akeneo_free_trial_invited_user (email, status, created_at) VALUES (:email, :status, NOW())
ON DUPLICATE KEY UPDATE status = :status;
SQL;

        $this->dbalConnection->executeQuery($query, [
            'email' => $invitedUser->getEmail(),
            'status' => $invitedUser->getStatus(),
        ]);
    }
}
