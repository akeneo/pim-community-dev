<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\MySql;

use Akeneo\FreeTrial\Domain\Model\InvitedUser;
use Akeneo\FreeTrial\Domain\Repository\InvitedUserRepository;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MySqlInvitedUserRepository implements InvitedUserRepository
{
    private Connection $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function save(InvitedUser $invitedUser): void
    {
        $query = <<<SQL
INSERT INTO akeneo_free_trial_invited_user (email, status, created_at) VALUES (:email, :status, NOW());
SQL;

        $this->dbalConnection->executeQuery($query, [
            'email' => $invitedUser->getEmail(),
            'status' => $invitedUser->getStatus(),
        ]);
    }
}