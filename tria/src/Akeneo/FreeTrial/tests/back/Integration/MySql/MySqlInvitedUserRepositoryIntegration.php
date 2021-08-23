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

namespace Akeneo\Test\FreeTrial\Integration\MySql;

use Akeneo\FreeTrial\Domain\Model\InvitedUser;
use Akeneo\FreeTrial\Domain\ValueObject\InvitedUserStatus;
use Akeneo\Test\Integration\TestCase;

final class MySqlInvitedUserRepositoryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_saves_a_new_invited_user()
    {
        $invitedUser = new InvitedUser('test_save1@test.com', InvitedUserStatus::invited());

        $this->get('Akeneo\FreeTrial\Domain\Repository\InvitedUserRepository')->save($invitedUser);

        $invitedUserTest = $this->findInvitedUserByEmail($invitedUser->getEmail());

        $this->assertSame('test_save1@test.com', $invitedUserTest['email']);
        $this->assertSame(InvitedUserStatus::INVITED, $invitedUserTest['status']);
    }

    public function test_it_updates_the_status_of_an_invited_user()
    {
        $invitedUser = new InvitedUser('test_save1@test.com', InvitedUserStatus::invited());
        $this->get('Akeneo\FreeTrial\Domain\Repository\InvitedUserRepository')->save($invitedUser);
        $invitedUserTest = $this->findInvitedUserByEmail($invitedUser->getEmail());
        $this->assertSame(InvitedUserStatus::INVITED, $invitedUserTest['status']);

        $invitedUser = new InvitedUser('test_save1@test.com', InvitedUserStatus::active());
        $this->get('Akeneo\FreeTrial\Domain\Repository\InvitedUserRepository')->save($invitedUser);

        $numberOfInvitedUsers = $this->get('database_connection')->executeQuery('SELECT COUNT(*) FROM akeneo_free_trial_invited_user')->fetchColumn();
        $this->assertSame(1, (int) $numberOfInvitedUsers);

        $invitedUserTest = $this->findInvitedUserByEmail($invitedUser->getEmail());
        $this->assertSame(InvitedUserStatus::ACTIVE, $invitedUserTest['status']);
    }

    private function findInvitedUserByEmail(string $email): array
    {
        return $this->get('database_connection')->executeQuery('
SELECT email, status 
FROM akeneo_free_trial_invited_user 
WHERE email = :email', ['email' => $email])->fetch(\PDO::FETCH_ASSOC);
    }
}
